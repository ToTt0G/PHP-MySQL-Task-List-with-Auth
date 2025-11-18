const taskSubmitForm = document.getElementById("task-submit-form");
const taskInput = document.getElementById("task-input");
const taskList = document.getElementById("task-list");
const clearTasksButton = document.getElementById("clear-tasks-button");
const logoutButton = document.getElementById("logout-button");

const trashcanIcon =
  '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><title>Delete</title><path d="M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" /><path d="M3 7h18" /><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7" /><path d="M9 11v8" /><path d="M12 11v8" /><path d="M15 11v8" /></svg>';

// Centralized state
let tasks = [];

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

// --- API Layer ---
const api = {
  async fetchTasks() {
    const response = await fetch("api/tasks");
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    return await response.json();
  },

  async addTask(task) {
    const response = await fetch("api/tasks", {
      method: "POST",
      headers: { 
        "Content-Type": "application/x-www-form-urlencoded",
        "X-CSRF-Token": getCsrfToken()
      },
      body: `task=${encodeURIComponent(task)}`,
    });
    if (!response.ok) throw new Error("Failed to add task");
    return await response.json();
  },

  async editTask(id, task) {
    const response = await fetch("api/tasks", {
      method: "POST",
      headers: { 
        "Content-Type": "application/x-www-form-urlencoded",
        "X-CSRF-Token": getCsrfToken()
      },
      body: `edit_task_id=${encodeURIComponent(
        id
      )}&edit_task=${encodeURIComponent(task)}`,
    });
    if (!response.ok) throw new Error("Failed to edit task");
    return await response.json();
  },

  async deleteTask(id) {
    const response = await fetch("api/tasks", {
      method: "POST",
      headers: { 
        "Content-Type": "application/x-www-form-urlencoded",
        "X-CSRF-Token": getCsrfToken()
      },
      body: `delete_task_id=${encodeURIComponent(id)}`,
    });
    if (!response.ok) throw new Error("Failed to delete task");
    return await response.json();
  },

  async clearAllTasks() {
    const response = await fetch("api/tasks", { 
      method: "DELETE",
      headers: {
        "X-CSRF-Token": getCsrfToken()
      }
    });
    if (!response.ok) throw new Error("Failed to clear tasks");
    return await response.json();
  },

  async pollForTasks(count) {
    const response = await fetch(`api/tasks?poll=true&count=${count}`);
    if (response.status === 204) {
      return null; // Indicates no new data, not an error.
    }
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    return await response.json();
  },

  async logout() {
    const response = await fetch("api/auth/logout", { 
      method: "POST",
      headers: {
        "X-CSRF-Token": getCsrfToken()
      }
    });
    if (!response.ok) throw new Error("Failed to logout");
    return await response.json();
  },
};

// --- UI / Rendering ---
function renderTasks() {
  taskList.innerHTML = "";
  if (tasks.length === 0) {
    const li = document.createElement("li");
    li.textContent = "No tasks yet. Add one above!";
    taskList.appendChild(li);
    return;
  }
  tasks.forEach((task) => {
    const li = createTaskElement(task);
    taskList.appendChild(li);
  });
}

// Loading skeleton
function showLoadingSkeleton(rows) {
  taskList.innerHTML = "";
  for (let i = 0; i < rows; i++) {
    const li = document.createElement("li");
    const w = Math.random() * 70 + 10;
    li.style.setProperty("--width-js", `${w}%`);
    li.className = "skeleton";
    taskList.appendChild(li);
  }
}

function createTaskElement(task) {
  const li = document.createElement("li");
  li.id = task.id;
  const span = document.createElement("span");
  span.textContent = task.value;

  span.addEventListener("click", (e) => editTaskEffect(task, e.currentTarget));

  const deleteBtn = document.createElement("button");
  deleteBtn.innerHTML = trashcanIcon;
  deleteBtn.classList.add("delete-button");
  deleteBtn.addEventListener("click", () => handleDeleteTask(task.id));

  li.append(span, deleteBtn);
  return li;
}

function editTaskEffect(task, span) {
  const originalValue = task.value;
  const input = document.createElement("input");
  input.type = "text";
  input.value = originalValue;
  input.classList.add("edit-input");

  const li = span.closest("li");
  if (li) li.classList.add("editing");

  // Function to handle saving the changes
  const handleSave = () => {
    const newValue = input.value.trim();

    // 1. Revert to span - do this first for a snappy UI response
    span.textContent = newValue || originalValue; // Show new value, or original if empty
    input.replaceWith(span);
    if (li) li.classList.remove("editing");

    // 2. Check if the value has actually changed and is not empty
    if (newValue && newValue !== originalValue) {
      task.value = newValue;

      // 3. Optimistically update the UI, then call the API
      api
        .editTask(task.id, newValue)
        .then(() => {
          // Success, UI is already updated, maybe show a subtle success toast?
          console.log(`Task ${task.id} updated to "${newValue}"`);
        })
        .catch((error) => {
          // 4. If API fails, revert the change in the UI and notify the user
          console.error("Failed to update task:", error);
          task.value = originalValue; // Revert in-memory task object
          span.textContent = originalValue; // Revert the displayed text
          showToast("Error: Could not save changes.", "error");
        });
    } else {
      // If no change or value is empty, just revert UI without API call
      span.textContent = originalValue;
    }
  };

  // Function to handle canceling the edit
  const handleCancel = () => {
    span.textContent = originalValue;
    input.replaceWith(span);
    if (li) li.classList.remove("editing");
  };

  input.addEventListener("blur", handleSave);

  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault(); // Prevents form submission if it's in a form
      handleSave();
    } else if (e.key === "Escape") {
      handleCancel();
    }
  });

  span.replaceWith(input);
  input.focus();

  // Place caret at the end
  requestAnimationFrame(() => {
    const end = input.value.length;
    try {
      input.setSelectionRange(end, end);
    } catch (e) {}
  });
}

// Toast
function errorToast(message) {
  const toast = document.createElement("div");
  toast.textContent = message;
  toast.className = "toast error";
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

// --- Event Handlers / App Logic ---
async function handleAddTask(event) {
  event.preventDefault();
  const taskValue = taskInput.value.trim();
  if (!taskValue) {
    errorToast("Please enter a task");
    return;
  }
  try {
    const result = await api.addTask(taskValue);
    if (result.success) {
      tasks.push({ value: taskValue, id: result.id });
      renderTasks();
      taskInput.value = "";
    } else {
      errorToast("Error adding task.");
    }
  } catch (error) {
    console.error("Add task error:", error);
    errorToast("An unexpected error occurred.");
  }
}

async function handleDeleteTask(id) {
  const originalTasks = [...tasks];
  tasks = tasks.filter((task) => task.id !== id);
  renderTasks(); // Optimistic update

  try {
    const result = await api.deleteTask(id);
    if (!result.success) {
      tasks = originalTasks; // Revert on failure
      renderTasks();
      errorToast("Error deleting task.");
    }
  } catch (error) {
    console.error("Delete task error:", error);
    tasks = originalTasks; // Revert on network error
    renderTasks();
    errorToast("An unexpected error occurred.");
  }
}

async function handleClearAllTasks() {
  const originalTasks = [...tasks];
  tasks = [];
  renderTasks(); // Optimistic update

  try {
    const result = await api.clearAllTasks();
    if (!result.success) {
      tasks = originalTasks; // Revert on failure
      renderTasks();
      errorToast("Error clearing tasks.");
    }
  } catch (error) {
    console.error("Clear tasks error:", error);
    tasks = originalTasks; // Revert on network error
    renderTasks();
    errorToast("An unexpected error occurred.");
  }
}

// --- Short Polling Logic ---
async function startShortPolling() {
  while (true) {
    try {
      const updatedTasks = await api.pollForTasks(tasks.length);
      // If the server sent back data (i.e., not null from a 204 No Content response)
      if (updatedTasks !== null) {
        tasks = updatedTasks;
        renderTasks();
      }
    } catch (error) {
      // Log polling errors quietly to avoid spamming user with toasts on network hiccups
      console.error("Polling error:", error);
      // Wait 5 seconds before retrying on error to avoid hammering the server
      await new Promise((resolve) => setTimeout(resolve, 5000));
    }
  }
}

// --- Initialization ---
async function initializeApp() {
  showLoadingSkeleton(3);
  try {
    tasks = await api.fetchTasks();
    renderTasks();
  } catch (error) {
    console.error("Initialization error:", error);
    errorToast("Could not load tasks.");
  }
  startShortPolling(); // Start polling after initial fetch
}

// --- Event Listeners ---
if (taskSubmitForm) {
  taskSubmitForm.addEventListener("submit", handleAddTask);
  clearTasksButton.addEventListener("click", handleClearAllTasks);
}

// Logout button event listener
if (logoutButton) {
  logoutButton.addEventListener("click", () => {
    api.logout();
    window.location.href = "/login";
  });
}

// --- App Start ---
initializeApp();
