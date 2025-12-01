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
  
  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  async function deleteSession(sessionId, listItem) {
      if (!confirm("Are you sure you want to revoke this session?")) return;

      try {
          const response = await fetch(`/api/admin/sessions/${sessionId}`, {
              method: "DELETE",
              headers: {
                  "X-CSRF-Token": getCsrfToken()
              }
          });

          if (response.ok) {
              listItem.remove();
          } else {
              alert("Failed to delete session");
          }
      } catch (error) {
          console.error("Error deleting session:", error);
          alert("Error deleting session");
      }
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
        let sessionsHtml;
        if (userSessions.length > 0) {
            const ul = document.createElement('ul');
            ul.className = 'list';
            userSessions.forEach(s => {
                const li = document.createElement('li');
                // Mask the token for display
                const maskedToken = s.session_token ? s.session_token.substring(0, 8) + '...' : 'N/A';
                
                li.innerHTML = `
                    <div style="margin-bottom: 8px;"><strong>ID:</strong> <code>${escapeHtml(s.id)}</code></div>
                    <div style="margin-bottom: 8px;"><strong>Token:</strong> <code>${escapeHtml(maskedToken)}</code></div>
                    <div style="margin-bottom: 8px;"><strong>Created:</strong> ${escapeHtml(s.created_at)}</div>
                    <div style="margin-bottom: 16px;"><strong>Expires:</strong> ${escapeHtml(s.expires_at)}</div>
                    <button class="button sm ghost danger">Revoke</button>
                `;
                
                const btn = li.querySelector('button');
                btn.addEventListener('click', () => deleteSession(s.id, li));
                ul.appendChild(li);
            });
            sessionsHtml = ul;
        } else {
            const div = document.createElement('div');
            div.className = 'empty';
            div.textContent = 'No current sessions';
            sessionsHtml = div;
        }

        // Build tasks list
        const tasksHtml =
          userTasks.length > 0
            ? `<ul class="list">
                ${userTasks
                  .map(
                    (t) => `
                  <li>
                    <div><strong>ID:</strong> <code>${escapeHtml(t.id)}</code></div>
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

          <div class="section sessions-section">
            <h3>Current Sessions (${userSessions.length})</h3>
          </div>

          <div class="section">
            <h3>Tasks (${userTasks.length})</h3>
            ${tasksHtml}
          </div>
        `;
        
        // Append the session list element (DOM object) to the correct section
        card.querySelector('.sessions-section').appendChild(sessionsHtml);

        container.appendChild(card);
      });
    } catch (err) {
      console.error("Failed to render admin dashboard:", err);
    }
  })();
});
