/* =========================================================
   ShopSphere — product catalog
   Static data standing in for a database-backed listing table.
   Each product carries its own stock count, which the cart
   depletes live as items are added — no separate "inventory"
   system needed for this demo.
   ========================================================= */

const PRODUCTS = [
  {
    id: "p01",
    name: "Ridge Ceramic Mug",
    category: "kitchen",
    price: 14.00,
    stock: 6,
    icon: "mug",
    hue: 18,
    popularity: 9,
    desc: "Matte-glazed stoneware, holds 350ml, dishwasher safe."
  },
  {
    id: "p02",
    name: "Fern Leaf Planter",
    category: "home",
    price: 22.50,
    stock: 3,
    icon: "leaf",
    hue: 142,
    popularity: 8,
    desc: "Terracotta planter with drainage dish, 15cm diameter."
  },
  {
    id: "p03",
    name: "Arc Desk Lamp",
    category: "home",
    price: 48.00,
    stock: 0,
    icon: "lamp",
    hue: 44,
    popularity: 10,
    desc: "Warm dimmable LED, adjustable arm, USB-C powered."
  },
  {
    id: "p04",
    name: "Canvas Tote Bag",
    category: "accessories",
    price: 19.00,
    stock: 12,
    icon: "bag",
    hue: 205,
    popularity: 7,
    desc: "12oz heavyweight canvas, reinforced handles, 30L."
  },
  {
    id: "p05",
    name: "Dot Grid Notebook",
    category: "stationery",
    price: 11.00,
    stock: 20,
    icon: "notebook",
    hue: 258,
    popularity: 9,
    desc: "160 pages, 100gsm paper, lay-flat binding."
  },
  {
    id: "p06",
    name: "Slate Coasters (Set of 4)",
    category: "kitchen",
    price: 16.50,
    stock: 5,
    icon: "coaster",
    hue: 210,
    popularity: 5,
    desc: "Natural slate with cork backing, felt-lined box."
  },
  {
    id: "p07",
    name: "Brushed Steel Water Bottle",
    category: "accessories",
    price: 27.00,
    stock: 8,
    icon: "bottle",
    hue: 190,
    popularity: 8,
    desc: "750ml double-wall insulated, keeps cold 24 hours."
  },
  {
    id: "p08",
    name: "Linen Throw Pillow",
    category: "home",
    price: 24.00,
    stock: 2,
    icon: "pillow",
    hue: 28,
    popularity: 6,
    desc: "100% linen cover, 45x45cm, feather-blend insert."
  },
  {
    id: "p09",
    name: "Gel Ink Pen Set",
    category: "stationery",
    price: 9.00,
    stock: 15,
    icon: "pen",
    hue: 340,
    popularity: 7,
    desc: "Set of 6, 0.5mm tip, quick-dry archival ink."
  },
  {
    id: "p10",
    name: "Bamboo Cutting Board",
    category: "kitchen",
    price: 21.00,
    stock: 4,
    icon: "board",
    hue: 34,
    popularity: 6,
    desc: "Reversible, juice groove, 38x25cm."
  },
];
