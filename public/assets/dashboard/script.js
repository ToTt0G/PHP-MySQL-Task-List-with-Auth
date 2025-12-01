document.addEventListener("DOMContentLoaded", function () {
  // Unified rendering for per-user cards

  async function fetchJson(url) {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`Failed to fetch ${url}`);
    return res.json();
  }

  function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str == null ? "" : String(str);
    return div.innerHTML;
  }

  (async () => {
    try {
      const [users, sessions, tasks] = await Promise.all([
        fetchJson("/api/admin/all-users"),
        fetchJson("/api/admin/all-sessions"),
        fetchJson("/api/admin/all-tasks"),
      ]);

      const container = document.getElementById("user-cards-container");
      if (!container) return;

      const now = new Date();

      // Group current sessions by user_id (filter out expired)
      const sessionsByUser = sessions
        .filter((s) => s.expires_at && new Date(s.expires_at) > now)
        .reduce((acc, s) => {
          (acc[s.user_id] ||= []).push(s);
          return acc;
        }, {});

      // Group tasks by user_id
      const tasksByUser = tasks.reduce((acc, t) => {
        (acc[t.user_id] ||= []).push(t);
        return acc;
      }, {});

      container.innerHTML = "";

      users.forEach((u) => {
        const userSessions = sessionsByUser[u.id] || [];
        const userTasks = tasksByUser[u.id] || [];

        const card = document.createElement("div");
        card.className = "user-card";

        // Build sessions list
        const sessionsHtml =
          userSessions.length > 0
            ? `<ul class="list">
                ${userSessions
                  .map(
                    (s) => `
                  <li>
                    <div>Session ID: <code>${escapeHtml(s.id)}</code></div>
                    <div>Token: <code>${escapeHtml(
                      s.session_token
                    )}</code></div>
                    <div>Created: ${escapeHtml(
                      s.created_at
                    )} · Expires: ${escapeHtml(s.expires_at)}</div>
                  </li>`
                  )
                  .join("")}
              </ul>`
            : `<div class="empty">No current sessions</div>`;

        // Build tasks list
        const tasksHtml =
          userTasks.length > 0
            ? `<ul class="list">
                ${userTasks
                  .map(
                    (t) => `
                  <li>
                    <div>Task ID: <code>${escapeHtml(t.id)}</code></div>
                    <div>${escapeHtml(t.value)}</div>
                  </li>`
                  )
                  .join("")}
              </ul>`
            : `<div class="empty">No tasks</div>`;

        card.innerHTML = `
          <h2>${escapeHtml(u.name)} <span class="badge">${escapeHtml(
          u.role
        )}</span></h2>
          <div class="meta">ID: <code>${escapeHtml(u.id)}</code></div>
          <div class="meta">Email: ${escapeHtml(u.email)}</div>
          <div class="meta">Joined: ${escapeHtml(u.created_at)}</div>

          <div class="section">
            <h3>Current Sessions (${userSessions.length})</h3>
            ${sessionsHtml}
          </div>

          <div class="section">
            <h3>Tasks (${userTasks.length})</h3>
            ${tasksHtml}
          </div>
        `;

        container.appendChild(card);
      });
    } catch (err) {
      console.error("Failed to render admin dashboard:", err);
    }
  })();
});
