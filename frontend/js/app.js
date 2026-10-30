const API_BASE = "../backend/";

async function apiRequest(url, options = {}) {
    const response = await fetch(url, options);
    let data;
    try {
        data = await response.json();
    } catch (error) {
        throw new Error('Server returned an invalid response');
    }
    if (!response.ok) {
        throw new Error(data.error || 'Request failed');
    }
    return data;
}

function showMessage(element, message, isError = false) {
    if (!element) return;
    element.textContent = message;
    element.style.color = isError ? 'red' : 'green';
}

async function loadPlayers() {
    const table = document.getElementById('players-table-body');
    if (!table) return;
    try {
        const players = await apiRequest(API_BASE + 'players.php');
        table.innerHTML = '';
        players.forEach((player) => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${escapeHtml(player.gamer_tag)}</td><td>${escapeHtml(player.full_name)}</td><td>${escapeHtml(player.email)}</td><td>${escapeHtml(player.country)}</td><td>${escapeHtml(player.main_character)}</td>`;
            table.appendChild(row);
        });
    } catch (error) {
        table.innerHTML = `<tr><td colspan="5">${escapeHtml(error.message)}</td></tr>`;
    }
}

async function loadMatches() {
    const table = document.getElementById('matches-table-body');
    if (!table) return;
    try {
        const matches = await apiRequest(API_BASE + 'matches.php');
        table.innerHTML = '';
        matches.forEach((match) => {
            const row = document.createElement('tr');
            const result = match.winner ? `${escapeHtml(match.winner)} (${escapeHtml(match.score || '')})` : 'Not played';
            row.innerHTML = `<td>${escapeHtml(match.round_name)}</td><td>${escapeHtml(match.player1)}</td><td>${escapeHtml(match.player2)}</td><td>${escapeHtml(match.match_time)}</td><td>${result}</td>`;
            table.appendChild(row);
        });
    } catch (error) {
        table.innerHTML = `<tr><td colspan="5">${escapeHtml(error.message)}</td></tr>`;
    }
}

async function loadResults() {
    const table = document.getElementById('results-table-body');
    if (!table) return;
    try {
        const results = await apiRequest(API_BASE + 'results.php');
        table.innerHTML = '';
        results.forEach((player, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${index + 1}</td><td>${escapeHtml(player.gamer_tag)}</td><td>${escapeHtml(player.matches_played)}</td><td>${escapeHtml(player.wins)}</td>`;
            table.appendChild(row);
        });
    } catch (error) {
        table.innerHTML = `<tr><td colspan="4">${escapeHtml(error.message)}</td></tr>`;
    }
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}

function setupRegistration() {
    const form = document.getElementById('registration-form');
    if (!form) return;
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = document.getElementById('registration-message');
        try {
            const data = await apiRequest(API_BASE + 'register.php', {
                method: 'POST',
                body: new FormData(form)
            });
            showMessage(message, data.success ? 'Player registered successfully.' : 'Registration failed.');
            if (data.success) form.reset();
        } catch (error) {
            showMessage(message, error.message, true);
        }
    });
}

async function loadAdminPlayers() {
    const password = document.getElementById('admin-password');
    if (!password || !password.value) return;
    try {
        const data = await apiRequest(API_BASE + 'admin.php', {
            method: 'POST',
            body: new URLSearchParams({ password: password.value, action: 'list_players' })
        });
        const player1 = document.getElementById('player1-id');
        const player2 = document.getElementById('player2-id');
        const winner = document.getElementById('winner-id');
        [player1, player2, winner].forEach((select) => {
            if (!select) return;
            select.innerHTML = '';
            data.players.forEach((player) => {
                const option = document.createElement('option');
                option.value = player.id;
                option.textContent = player.gamer_tag;
                select.appendChild(option);
            });
        });
    } catch (error) {
        showMessage(document.getElementById('admin-message'), error.message, true);
    }
}

function setupAdmin() {
    const password = document.getElementById('admin-password');
    if (!password) return;

    password.addEventListener('change', loadAdminPlayers);

    const addForm = document.getElementById('add-match-form');
    addForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = new URLSearchParams(new FormData(addForm));
        body.set('match_time', body.get('match_time').replace('T', ' '));
        body.set('password', password.value);
        body.set('action', 'add_match');
        try {
            await apiRequest(API_BASE + 'admin.php', { method: 'POST', body });
            showMessage(document.getElementById('admin-message'), 'Match added successfully.');
        } catch (error) {
            showMessage(document.getElementById('admin-message'), error.message, true);
        }
    });

    const resultForm = document.getElementById('set-result-form');
    resultForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = new URLSearchParams(new FormData(resultForm));
        body.set('password', password.value);
        body.set('action', 'set_result');
        try {
            await apiRequest(API_BASE + 'admin.php', { method: 'POST', body });
            showMessage(document.getElementById('admin-message'), 'Result saved successfully.');
        } catch (error) {
            showMessage(document.getElementById('admin-message'), error.message, true);
        }
    });
}

loadPlayers();
loadMatches();
loadResults();
setupRegistration();
setupAdmin();
