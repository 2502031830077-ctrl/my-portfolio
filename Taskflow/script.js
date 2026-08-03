/* =========================================================
   TaskFlow — vanilla JS task manager
   Storage: localStorage (client-side only, no backend)
   Command syntax parsed from the input bar:
     !high / !medium / !low   -> sets priority
     #tag                     -> sets a category tag
   Everything else typed becomes the task text.
   ========================================================= */

const STORAGE_KEY = "taskflow.tasks.v1";

/** @type {{id:string, text:string, priority:'low'|'medium'|'high', tag:string|null, done:boolean, createdAt:number}[]} */
let tasks = loadTasks();
let activeFilter = "all";     // all | active | done
let activeTag = null;         // string | null
let searchTerm = "";

// ---------- DOM refs ----------
const cmdInput      = document.getElementById("cmdInput");
const cmdRun        = document.getElementById("cmdRun");
const taskList      = document.getElementById("taskList");
const emptyState    = document.getElementById("emptyState");
const emptyMsg      = document.getElementById("emptyMsg");
const filterTabs    = document.getElementById("filterTabs");
const filterTagsWrap= document.getElementById("filterTags");
const searchInput   = document.getElementById("searchInput");
const statTotal     = document.getElementById("statTotal");
const statActive    = document.getElementById("statActive");
const statDone      = document.getElementById("statDone");
const statPct       = document.getElementById("statPct");
const progressFill  = document.getElementById("progressFill");
const saveDot       = document.getElementById("saveDot");
const saveLabel     = document.getElementById("saveLabel");

// ---------- Storage ----------
function loadTasks() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch (err) {
    console.error("TaskFlow: could not read saved tasks", err);
    return [];
  }
}

function saveTasks() {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks));
    flashSaved();
  } catch (err) {
    console.error("TaskFlow: could not save tasks", err);
    saveLabel.textContent = "save failed — storage full or blocked";
  }
}

function flashSaved() {
  saveLabel.textContent = "saved locally";
  saveDot.classList.remove("pulse");
  void saveDot.offsetWidth; // restart animation
  saveDot.classList.add("pulse");
}

// ---------- Command parsing ----------
function parseCommand(raw) {
  let text = raw.trim();
  let priority = "medium";
  let tag = null;

  const priorityMatch = text.match(/!(\bhigh\b|\bmedium\b|\blow\b)/i);
  if (priorityMatch) {
    priority = priorityMatch[1].toLowerCase();
    text = text.replace(priorityMatch[0], "").trim();
  }

  const tagMatch = text.match(/#([a-zA-Z0-9_-]+)/);
  if (tagMatch) {
    tag = tagMatch[1].toLowerCase();
    text = text.replace(tagMatch[0], "").trim();
  }

  text = text.replace(/\s{2,}/g, " ").trim();
  return { text, priority, tag };
}

function addTaskFromInput() {
  const raw = cmdInput.value;
  if (!raw.trim()) return;

  const { text, priority, tag } = parseCommand(raw);
  if (!text) {
    cmdInput.value = "";
    return;
  }

  tasks.unshift({
    id: `t_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`,
    text,
    priority,
    tag,
    done: false,
    createdAt: Date.now(),
  });

  cmdInput.value = "";
  saveTasks();
  render();
}

// ---------- Actions ----------
function toggleDone(id) {
  const t = tasks.find(t => t.id === id);
  if (!t) return;
  t.done = !t.done;
  saveTasks();
  render();
}

function deleteTask(id) {
  tasks = tasks.filter(t => t.id !== id);
  saveTasks();
  render();
}

// ---------- Derived data ----------
function getAllTags() {
  const set = new Set();
  tasks.forEach(t => { if (t.tag) set.add(t.tag); });
  return Array.from(set).sort();
}

function getVisibleTasks() {
  return tasks.filter(t => {
    if (activeFilter === "active" && t.done) return false;
    if (activeFilter === "done" && !t.done) return false;
    if (activeTag && t.tag !== activeTag) return false;
    if (searchTerm && !t.text.toLowerCase().includes(searchTerm)) return false;
    return true;
  });
}

function formatTime(ts) {
  const d = new Date(ts);
  const now = new Date();
  const sameDay = d.toDateString() === now.toDateString();
  if (sameDay) {
    return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
  }
  return d.toLocaleDateString([], { month: "short", day: "numeric" });
}

// ---------- Rendering ----------
function render() {
  renderStats();
  renderTagFilters();
  renderList();
}

function renderStats() {
  const total = tasks.length;
  const done = tasks.filter(t => t.done).length;
  const active = total - done;
  const pct = total === 0 ? 0 : Math.round((done / total) * 100);

  statTotal.textContent = total;
  statActive.textContent = active;
  statDone.textContent = done;
  statPct.textContent = `${pct}%`;
  progressFill.style.width = `${pct}%`;
  document.getElementById("progressBar").setAttribute("aria-valuenow", String(pct));
}

function renderTagFilters() {
  const tags = getAllTags();
  filterTagsWrap.innerHTML = "";
  if (activeTag && !tags.includes(activeTag)) activeTag = null;

  tags.forEach(tag => {
    const chip = document.createElement("button");
    chip.className = "tag-chip" + (activeTag === tag ? " is-active" : "");
    chip.textContent = `#${tag}`;
    chip.type = "button";
    chip.addEventListener("click", () => {
      activeTag = activeTag === tag ? null : tag;
      render();
    });
    filterTagsWrap.appendChild(chip);
  });
}

function renderList() {
  const visible = getVisibleTasks();
  taskList.innerHTML = "";

  if (visible.length === 0) {
    emptyState.hidden = false;
    emptyMsg.textContent = tasks.length === 0
      ? "no tasks yet"
      : "nothing matches this view";
  } else {
    emptyState.hidden = true;
  }

  visible.forEach(t => {
    const li = document.createElement("li");
    li.className = "task-row" + (t.done ? " is-done" : "");
    li.dataset.priority = t.priority;
    li.tabIndex = 0;
    li.setAttribute("role", "checkbox");
    li.setAttribute("aria-checked", String(t.done));

    li.innerHTML = `
      <span class="task-check" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="#0D1117" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </span>
      <span class="task-body">
        <span class="task-text"></span>
        <span class="task-meta">
          <span class="pill ${t.priority}">${t.priority}</span>
          ${t.tag ? `<span class="pill tag">#${t.tag}</span>` : ""}
          <span class="task-time">${formatTime(t.createdAt)}</span>
        </span>
      </span>
      <button class="task-del" type="button" aria-label="Delete task">×</button>
    `;

    li.querySelector(".task-text").textContent = t.text; // safe text insertion

    li.addEventListener("click", (e) => {
      if (e.target.closest(".task-del")) return;
      toggleDone(t.id);
    });
    li.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        toggleDone(t.id);
      }
    });
    li.querySelector(".task-del").addEventListener("click", (e) => {
      e.stopPropagation();
      deleteTask(t.id);
    });

    taskList.appendChild(li);
  });
}

// ---------- Event wiring ----------
cmdRun.addEventListener("click", addTaskFromInput);

cmdInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    addTaskFromInput();
  } else if (e.key === "Escape") {
    cmdInput.value = "";
  }
});

filterTabs.addEventListener("click", (e) => {
  const btn = e.target.closest(".filter-tab");
  if (!btn) return;
  activeFilter = btn.dataset.filter;
  [...filterTabs.children].forEach(c => c.classList.remove("is-active"));
  btn.classList.add("is-active");
  render();
});

searchInput.addEventListener("input", (e) => {
  searchTerm = e.target.value.trim().toLowerCase();
  render();
});

// ---------- Init ----------
render();
