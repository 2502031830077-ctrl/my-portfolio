/* =========================================================
   ShopSphere — vanilla JS storefront logic
   Cart persists in localStorage. Stock shown per product is
   live: total stock minus whatever quantity is currently in
   the cart, so the shelf always reflects what's actually
   available to add.
   ========================================================= */

const CART_KEY = "shopsphere.cart.v1";

let cart = loadCart();          // { [productId]: quantity }
let activeCategory = "all";
let searchTerm = "";
let sortMode = "popular";

// ---------- DOM refs ----------
const productGrid   = document.getElementById("productGrid");
const emptyState    = document.getElementById("emptyState");
const catTabs       = document.getElementById("catTabs");
const searchInput   = document.getElementById("searchInput");
const sortSelect    = document.getElementById("sortSelect");
const clearFilters  = document.getElementById("clearFilters");

const cartToggle    = document.getElementById("cartToggle");
const cartClose     = document.getElementById("cartClose");
const cartDrawer    = document.getElementById("cartDrawer");
const overlay       = document.getElementById("overlay");
const cartItemsEl   = document.getElementById("cartItems");
const cartEmptyEl   = document.getElementById("cartEmpty");
const cartFooterEl  = document.getElementById("cartFooter");
const cartSubtotal  = document.getElementById("cartSubtotal");
const cartCountEl   = document.getElementById("cartCount");
const checkoutBtn   = document.getElementById("checkoutBtn");

const modalOverlay  = document.getElementById("modalOverlay");
const modalOrderId  = document.getElementById("modalOrderId");
const modalClose    = document.getElementById("modalClose");

// ---------- Storage ----------
function loadCart() {
  try {
    const raw = localStorage.getItem(CART_KEY);
    return raw ? JSON.parse(raw) : {};
  } catch (err) {
    console.error("ShopSphere: could not read saved cart", err);
    return {};
  }
}

function saveCart() {
  try {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  } catch (err) {
    console.error("ShopSphere: could not save cart", err);
  }
}

// ---------- Icons (inline SVG per category icon key) ----------
const ICONS = {
  mug: '<path d="M4 8h13v7a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8Z"/><path d="M17 10h1.5a2.5 2.5 0 0 1 0 5H17"/><line x1="7" y1="4" x2="7" y2="6"/><line x1="10.5" y1="3" x2="10.5" y2="5.5"/>',
  leaf: '<path d="M4 20c8 0 16-6 16-16C10 4 4 12 4 20Z"/><path d="M4 20c3-6 7-9 11-11"/>',
  lamp: '<path d="M9 3h6l3 8H6l3-8Z"/><line x1="12" y1="11" x2="12" y2="18"/><path d="M8 21h8"/>',
  bag: '<path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
  notebook: '<rect x="5" y="3" width="14" height="18" rx="1.5"/><line x1="8" y1="7" x2="15" y2="7"/><line x1="8" y1="11" x2="15" y2="11"/><line x1="8" y1="15" x2="12" y2="15"/>',
  coaster: '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3.2"/>',
  bottle: '<path d="M10 2h4v3l2 2v13a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2V7l2-2V2Z"/><line x1="8" y1="13" x2="16" y2="13"/>',
  pillow: '<path d="M4 6c2-1 4 1 6 0s4-1 6 0 4 1 4 6-2 5-4 6-4-1-6 0-4 1-6 0-4-1-4-6 2-5 4-6Z"/>',
  pen: '<path d="M4 20l1-5L16 4l4 4L9 19l-5 1Z"/><line x1="14" y1="6" x2="18" y2="10"/>',
  board: '<rect x="3" y="6" width="18" height="13" rx="2"/><circle cx="17" cy="10" r="1"/>',
};

function iconSvg(key, size = 24) {
  const path = ICONS[key] || ICONS.mug;
  return `<svg viewBox="0 0 24 24" width="${size}" height="${size}" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">${path}</svg>`;
}

function thumbStyle(hue) {
  return `background: linear-gradient(155deg, hsl(${hue} 55% 92%), hsl(${hue} 45% 85%)); color: hsl(${hue} 45% 32%);`;
}

// ---------- Derived stock ----------
function availableStock(product) {
  const inCart = cart[product.id] || 0;
  return Math.max(product.stock - inCart, 0);
}

// ---------- Rendering: product grid ----------
function getFilteredProducts() {
  let list = PRODUCTS.filter(p => {
    if (activeCategory !== "all" && p.category !== activeCategory) return false;
    if (searchTerm && !p.name.toLowerCase().includes(searchTerm)) return false;
    return true;
  });

  switch (sortMode) {
    case "price-asc": list = list.slice().sort((a, b) => a.price - b.price); break;
    case "price-desc": list = list.slice().sort((a, b) => b.price - a.price); break;
    case "stock": list = list.slice().sort((a, b) => availableStock(b) - availableStock(a)); break;
    default: list = list.slice().sort((a, b) => b.popularity - a.popularity);
  }
  return list;
}

function renderGrid() {
  const list = getFilteredProducts();
  productGrid.innerHTML = "";

  if (list.length === 0) {
    emptyState.hidden = false;
    return;
  }
  emptyState.hidden = true;

  list.forEach(p => {
    const stock = availableStock(p);
    const pct = Math.min(100, Math.round((stock / Math.max(p.stock, 1)) * 100));
    const stockClass = stock === 0 ? "out" : stock <= 3 ? "low" : "";

    const card = document.createElement("article");
    card.className = "product-card";
    card.innerHTML = `
      <div class="product-thumb" style="${thumbStyle(p.hue)}">
        ${stock === 0 ? `<span class="stock-badge out">Sold out</span>` : (stock <= 3 ? `<span class="stock-badge low">Only ${stock} left</span>` : "")}
        ${iconSvg(p.icon, 56)}
      </div>
      <div class="product-info">
        <h3 class="product-name"></h3>
        <p class="product-desc"></p>
        <div class="stock-bar-track"><div class="stock-bar-fill ${stockClass}" style="width:${pct}%"></div></div>
        <span class="stock-label">${stock} in stock</span>
        <div class="product-bottom">
          <span class="product-price">$${p.price.toFixed(2)}</span>
          <button class="add-btn" data-id="${p.id}" ${stock === 0 ? "disabled" : ""}>${stock === 0 ? "Sold out" : "Add to cart"}</button>
        </div>
      </div>
    `;
    card.querySelector(".product-name").textContent = p.name;
    card.querySelector(".product-desc").textContent = p.desc;
    productGrid.appendChild(card);
  });
}

// ---------- Cart actions ----------
function addToCart(id) {
  const product = PRODUCTS.find(p => p.id === id);
  if (!product) return;
  const current = cart[id] || 0;
  if (current >= product.stock) return; // no more available
  cart[id] = current + 1;
  saveCart();
  renderAll();
  bumpCartCount();
}

function changeQty(id, delta) {
  const product = PRODUCTS.find(p => p.id === id);
  if (!product) return;
  const next = (cart[id] || 0) + delta;
  if (next <= 0) {
    delete cart[id];
  } else if (next <= product.stock) {
    cart[id] = next;
  }
  saveCart();
  renderAll();
}

function removeFromCart(id) {
  delete cart[id];
  saveCart();
  renderAll();
}

function cartEntries() {
  return Object.entries(cart)
    .map(([id, qty]) => ({ product: PRODUCTS.find(p => p.id === id), qty }))
    .filter(e => e.product);
}

function cartTotal() {
  return cartEntries().reduce((sum, e) => sum + e.product.price * e.qty, 0);
}

function cartCount() {
  return Object.values(cart).reduce((sum, q) => sum + q, 0);
}

function bumpCartCount() {
  cartCountEl.classList.remove("pulse");
  void cartCountEl.offsetWidth;
  cartCountEl.classList.add("pulse");
}

// ---------- Rendering: cart drawer ----------
function renderCart() {
  const entries = cartEntries();
  cartItemsEl.innerHTML = "";
  cartCountEl.textContent = cartCount();

  if (entries.length === 0) {
    cartEmptyEl.style.display = "flex";
    cartFooterEl.style.display = "none";
    cartItemsEl.style.display = "none";
    return;
  }
  cartEmptyEl.style.display = "none";
  cartFooterEl.style.display = "block";
  cartItemsEl.style.display = "flex";

  entries.forEach(({ product, qty }) => {
    const row = document.createElement("div");
    row.className = "cart-item";
    row.innerHTML = `
      <div class="cart-item-thumb" style="${thumbStyle(product.hue)}">${iconSvg(product.icon, 24)}</div>
      <div class="cart-item-info">
        <p class="cart-item-name"></p>
        <span class="cart-item-price">$${product.price.toFixed(2)} each</span>
        <div class="qty-row">
          <button class="qty-btn" data-action="dec" data-id="${product.id}">−</button>
          <span class="qty-val">${qty}</span>
          <button class="qty-btn" data-action="inc" data-id="${product.id}" ${qty >= product.stock ? "disabled" : ""}>+</button>
          <button class="remove-btn" data-id="${product.id}">Remove</button>
        </div>
      </div>
    `;
    row.querySelector(".cart-item-name").textContent = product.name;
    cartItemsEl.appendChild(row);
  });

  cartSubtotal.textContent = `$${cartTotal().toFixed(2)}`;
}

function renderAll() {
  renderGrid();
  renderCart();
}

// ---------- Drawer open/close ----------
function openCart() {
  cartDrawer.classList.add("is-open");
  overlay.classList.add("is-open");
}
function closeCart() {
  cartDrawer.classList.remove("is-open");
  overlay.classList.remove("is-open");
}

// ---------- Checkout ----------
function placeOrder() {
  if (cartCount() === 0) return;
  const orderId = "SS-" + Math.random().toString(36).slice(2, 8).toUpperCase();
  modalOrderId.textContent = `Order #${orderId}`;
  cart = {};
  saveCart();
  renderAll();
  closeCart();
  modalOverlay.classList.add("is-open");
}

// ---------- Event wiring ----------
productGrid.addEventListener("click", (e) => {
  const btn = e.target.closest(".add-btn");
  if (!btn || btn.disabled) return;
  addToCart(btn.dataset.id);
});

catTabs.addEventListener("click", (e) => {
  const btn = e.target.closest(".cat-tab");
  if (!btn) return;
  activeCategory = btn.dataset.cat;
  [...catTabs.children].forEach(c => c.classList.remove("is-active"));
  btn.classList.add("is-active");
  renderGrid();
});

searchInput.addEventListener("input", (e) => {
  searchTerm = e.target.value.trim().toLowerCase();
  renderGrid();
});

sortSelect.addEventListener("change", (e) => {
  sortMode = e.target.value;
  renderGrid();
});

clearFilters.addEventListener("click", () => {
  activeCategory = "all";
  searchTerm = "";
  searchInput.value = "";
  [...catTabs.children].forEach(c => c.classList.remove("is-active"));
  catTabs.firstElementChild.classList.add("is-active");
  renderGrid();
});

cartToggle.addEventListener("click", openCart);
cartClose.addEventListener("click", closeCart);
overlay.addEventListener("click", closeCart);

cartItemsEl.addEventListener("click", (e) => {
  const qtyBtn = e.target.closest(".qty-btn");
  const removeBtn = e.target.closest(".remove-btn");
  if (qtyBtn) {
    changeQty(qtyBtn.dataset.id, qtyBtn.dataset.action === "inc" ? 1 : -1);
  } else if (removeBtn) {
    removeFromCart(removeBtn.dataset.id);
  }
});

checkoutBtn.addEventListener("click", placeOrder);
modalClose.addEventListener("click", () => modalOverlay.classList.remove("is-open"));
modalOverlay.addEventListener("click", (e) => {
  if (e.target === modalOverlay) modalOverlay.classList.remove("is-open");
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    closeCart();
    modalOverlay.classList.remove("is-open");
  }
});

// ---------- Init ----------
renderAll();
