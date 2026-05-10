<?php
require_once __DIR__ . '/config.php';
requireAuth();
$menu = readMenu();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — Золотая Рыбка Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Oswald:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="dashboard-page">

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <span class="s-fire">🔥</span>
    <div>
      <span class="s-brand">ЗОЛОТАЯ РЫБКА</span>
      <span class="s-sub">Admin Panel</span>
    </div>
  </div>
  <nav class="sidebar-nav">
    <a href="#" class="s-nav-item active" data-section="menu">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1" ry="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
      Меню
    </a>
    <a href="../index.html" target="_blank" class="s-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Открыть сайт
    </a>
    <a href="logout.php" class="s-nav-item s-nav-logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Выйти
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-status">
      <div class="status-dot"></div>
      Меню активно
    </div>
    <div class="sidebar-updated">Обновлено: <?= date('d.m.Y H:i', strtotime($menu['updated'] ?? 'now')) ?></div>
  </div>
</aside>

<!-- ===== MAIN ===== -->
<div class="main-wrap">
  <!-- Header -->
  <header class="top-bar">
    <button class="burger-btn" id="sidebarToggle">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="top-bar-title" id="pageTitle">Управление меню</div>
    <div class="top-bar-actions">
      <a href="../index.html" target="_blank" class="btn-sm btn-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Сайт
      </a>
    </div>
  </header>

  <!-- Toast -->
  <div class="toast" id="toast"></div>

  <!-- Content -->
  <div class="content">
    <!-- Category tabs -->
    <div class="cat-tabs" id="catTabs">
      <?php foreach ($menu['categories'] as $i => $cat): ?>
        <button class="cat-tab<?= $i === 0 ? ' active' : '' ?>" data-cat="<?= $cat['id'] ?>">
          <span><?= $cat['icon'] ?></span>
          <?= htmlspecialchars($cat['name']) ?>
          <span class="cat-tab-count">
            <?= isset($cat['drink_categories'])
              ? array_sum(array_map(fn($dc) => count($dc['items']), $cat['drink_categories']))
              : count($cat['items'] ?? []) ?>
          </span>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Category panels -->
    <?php foreach ($menu['categories'] as $ci => $cat): ?>
    <div class="cat-panel<?= $ci === 0 ? ' active' : '' ?>" id="cat-<?= $cat['id'] ?>">
      <div class="panel-header">
        <h2><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></h2>
        <?php if ($cat['id'] !== 'drinks'): ?>
        <button class="btn-primary btn-add" onclick="openAddModal('<?= $cat['id'] ?>', <?= $ci ?>)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Добавить блюдо
        </button>
        <?php endif; ?>
      </div>

      <?php if ($cat['id'] === 'drinks'): ?>
        <!-- Drinks view -->
        <?php foreach ($cat['drink_categories'] as $dci => $dc): ?>
        <div class="drinks-group">
          <div class="drinks-group-title"><?= htmlspecialchars($dc['title']) ?></div>
          <div class="drinks-table">
            <?php foreach ($dc['items'] as $dii => $drink): ?>
            <div class="drinks-row" data-cat="<?= $cat['id'] ?>" data-dci="<?= $dci ?>" data-dii="<?= $dii ?>">
              <div class="drinks-row-name"><?= htmlspecialchars($drink['name']) ?></div>
              <div class="drinks-row-vol"><?= htmlspecialchars($drink['volume'] ?? '—') ?></div>
              <div class="drinks-row-price"><?= $drink['price'] ? number_format($drink['price'], 0, '.', ' ') . ' ₽' : 'по запросу' ?></div>
              <div class="drinks-row-actions">
                <button class="btn-icon btn-vis<?= ($drink['visible'] ?? true) ? '' : ' inactive' ?>"
                  onclick="toggleDrinkVisible('<?= $cat['id'] ?>', <?= $dci ?>, <?= $dii ?>)"
                  title="<?= ($drink['visible'] ?? true) ? 'Скрыть' : 'Показать' ?>">
                  <?= ($drink['visible'] ?? true) ? '👁' : '🙈' ?>
                </button>
                <button class="btn-icon btn-edit" onclick="openDrinkEdit('<?= $cat['id'] ?>', <?= $dci ?>, <?= $dii ?>)" title="Редактировать">✏️</button>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button class="btn-sm btn-outline" style="margin:8px 0 0" onclick="openAddDrink('<?= $cat['id'] ?>', <?= $dci ?>)">
            + Добавить напиток
          </button>
        </div>
        <?php endforeach; ?>

      <?php else: ?>
        <!-- Regular items grid -->
        <div class="items-grid" id="grid-<?= $cat['id'] ?>">
          <?php foreach ($cat['items'] as $ii => $item): ?>
          <div class="item-card<?= ($item['visible'] ?? true) ? '' : ' item-hidden' ?>" data-id="<?= $item['id'] ?>" data-ci="<?= $ci ?>" data-ii="<?= $ii ?>">
            <div class="item-card-img">
              <?php if (!empty($item['image'])): ?>
                <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
              <?php else: ?>
                <div class="item-card-placeholder"><?= $cat['icon'] ?></div>
              <?php endif; ?>
              <?php if (!empty($item['badge_text'])): ?>
                <div class="item-badge item-badge--<?= $item['badge'] ?>"><?= $item['badge_text'] ?></div>
              <?php endif; ?>
              <?php if (!($item['visible'] ?? true)): ?>
                <div class="hidden-overlay">Скрыто</div>
              <?php endif; ?>
            </div>
            <div class="item-card-body">
              <div class="item-card-title"><?= htmlspecialchars($item['name']) ?></div>
              <div class="item-card-meta">
                <?php if ($item['weight']): ?><span>⚖️ <?= $item['weight'] ?></span><?php endif; ?>
                <?php if ($item['calories']): ?><span>🔥 <?= $item['calories'] ?></span><?php endif; ?>
              </div>
              <div class="item-card-price"><?= number_format($item['price'], 0, '.', ' ') ?> ₽</div>
            </div>
            <div class="item-card-actions">
              <button class="btn-icon btn-vis<?= ($item['visible'] ?? true) ? '' : ' inactive' ?>"
                onclick="toggleVisible('<?= $item['id'] ?>', <?= $ci ?>, <?= $ii ?>)"
                title="<?= ($item['visible'] ?? true) ? 'Скрыть' : 'Показать' ?>">
                <?= ($item['visible'] ?? true) ? '👁' : '🙈' ?>
              </button>
              <button class="btn-icon btn-edit" onclick="openEditModal('<?= $item['id'] ?>', <?= $ci ?>, <?= $ii ?>)" title="Редактировать">✏️</button>
              <button class="btn-icon btn-delete" onclick="deleteItem('<?= $item['id'] ?>', <?= $ci ?>, <?= $ii ?>)" title="Удалить">🗑</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ===== MODAL: Add/Edit Item ===== -->
<div class="modal-overlay" id="itemModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="modalTitle">Добавить блюдо</h3>
      <button class="modal-close" onclick="closeModal()">×</button>
    </div>
    <form id="itemForm" onsubmit="saveItem(event)">
      <input type="hidden" id="f_action" name="action" value="save_item" />
      <input type="hidden" id="f_id" name="id" value="" />
      <input type="hidden" id="f_ci" name="ci" value="" />
      <input type="hidden" id="f_ii" name="ii" value="-1" />
      <input type="hidden" id="f_image" name="image" value="" />

      <div class="form-row form-row--2">
        <div class="field">
          <label>Название *</label>
          <input type="text" id="f_name" name="name" placeholder="Шашлык из..." required />
        </div>
        <div class="field">
          <label>Цена (₽) *</label>
          <input type="number" id="f_price" name="price" placeholder="500" required min="0" />
        </div>
      </div>

      <div class="field">
        <label>Описание</label>
        <textarea id="f_description" name="description" rows="3" placeholder="Описание блюда..."></textarea>
      </div>

      <div class="form-row form-row--3">
        <div class="field">
          <label>Вес</label>
          <input type="text" id="f_weight" name="weight" placeholder="300 г" />
        </div>
        <div class="field">
          <label>Калории</label>
          <input type="text" id="f_calories" name="calories" placeholder="200 ккал" />
        </div>
        <div class="field">
          <label>Белки/жиры</label>
          <input type="text" id="f_protein" name="protein" placeholder="20г белка" />
        </div>
      </div>

      <div class="form-row form-row--2">
        <div class="field">
          <label>Тип бейджа</label>
          <select id="f_badge" name="badge" onchange="updateBadgeText()">
            <option value="">Нет</option>
            <option value="popular">🔥 Хит</option>
            <option value="exclusive">✨ Премиум / Эксклюзив</option>
            <option value="new">🆕 Новинка</option>
          </select>
        </div>
        <div class="field">
          <label>Текст бейджа</label>
          <input type="text" id="f_badge_text" name="badge_text" placeholder="🔥 Хит" />
        </div>
      </div>

      <!-- Photo upload -->
      <div class="field">
        <label>Фото блюда</label>
        <div class="upload-zone" id="uploadZone">
          <div class="upload-preview" id="uploadPreview">
            <div class="upload-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <span>Нажмите или перетащите фото</span>
              <small>JPG, PNG, WEBP до 5 МБ</small>
            </div>
          </div>
          <input type="file" id="photoInput" accept="image/*" onchange="handlePhoto(this)" />
        </div>
        <div id="uploadStatus"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeModal()">Отмена</button>
        <button type="submit" class="btn-primary" id="saveBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><polyline points="20 6 9 17 4 12"/></svg>
          Сохранить
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL: Add/Edit Drink ===== -->
<div class="modal-overlay" id="drinkModal">
  <div class="modal-box modal-box--sm">
    <div class="modal-header">
      <h3 id="drinkModalTitle">Напиток</h3>
      <button class="modal-close" onclick="closeDrinkModal()">×</button>
    </div>
    <form id="drinkForm" onsubmit="saveDrink(event)">
      <input type="hidden" id="df_action" name="action" value="save_drink" />
      <input type="hidden" id="df_cat" name="cat_id" value="" />
      <input type="hidden" id="df_dci" name="dci" value="" />
      <input type="hidden" id="df_dii" name="dii" value="-1" />

      <div class="field"><label>Название *</label>
        <input type="text" id="df_name" name="name" required placeholder="Кофе Американо" /></div>
      <div class="form-row form-row--2">
        <div class="field"><label>Объём</label>
          <input type="text" id="df_volume" name="volume" placeholder="0,5 л" /></div>
        <div class="field"><label>Цена (₽)</label>
          <input type="number" id="df_price" name="price" min="0" placeholder="150" /></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeDrinkModal()">Отмена</button>
        <button type="submit" class="btn-primary">Сохранить</button>
      </div>
    </form>
  </div>
</div>

<!-- ===== JS ===== -->
<script>
// ---- Category tabs ----
document.querySelectorAll('.cat-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.cat-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('cat-' + btn.dataset.cat).classList.add('active');
  });
});

// ---- Sidebar toggle ----
document.getElementById('sidebarToggle').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
});

// ---- Toast ----
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show toast--' + type;
  setTimeout(() => t.className = 'toast', 3000);
}

// ---- Item Modal ----
let currentUploadPath = '';
function openAddModal(catId, ci) {
  document.getElementById('modalTitle').textContent = 'Добавить блюдо';
  document.getElementById('f_action').value = 'save_item';
  document.getElementById('f_id').value = '';
  document.getElementById('f_ci').value = ci;
  document.getElementById('f_ii').value = -1;
  document.getElementById('f_name').value = '';
  document.getElementById('f_price').value = '';
  document.getElementById('f_description').value = '';
  document.getElementById('f_weight').value = '';
  document.getElementById('f_calories').value = '';
  document.getElementById('f_protein').value = '';
  document.getElementById('f_badge').value = '';
  document.getElementById('f_badge_text').value = '';
  document.getElementById('f_image').value = '';
  currentUploadPath = '';
  resetUploadPreview();
  document.getElementById('itemModal').classList.add('active');
}

function openEditModal(id, ci, ii) {
  fetch('api.php?action=get_item&ci=' + ci + '&ii=' + ii)
    .then(r => r.json()).then(item => {
      document.getElementById('modalTitle').textContent = 'Редактировать блюдо';
      document.getElementById('f_action').value = 'save_item';
      document.getElementById('f_id').value = item.id;
      document.getElementById('f_ci').value = ci;
      document.getElementById('f_ii').value = ii;
      document.getElementById('f_name').value = item.name;
      document.getElementById('f_price').value = item.price;
      document.getElementById('f_description').value = item.description;
      document.getElementById('f_weight').value = item.weight;
      document.getElementById('f_calories').value = item.calories;
      document.getElementById('f_protein').value = item.protein;
      document.getElementById('f_badge').value = item.badge;
      document.getElementById('f_badge_text').value = item.badge_text;
      document.getElementById('f_image').value = item.image;
      currentUploadPath = item.image;
      if (item.image) {
        document.getElementById('uploadPreview').innerHTML =
          `<img src="../${item.image}" style="width:100%;height:180px;object-fit:cover;border-radius:8px" />
           <button type="button" class="upload-clear" onclick="clearPhoto()">× Удалить фото</button>`;
      } else {
        resetUploadPreview();
      }
      document.getElementById('itemModal').classList.add('active');
    });
}

function closeModal() {
  document.getElementById('itemModal').classList.remove('active');
}

function resetUploadPreview() {
  document.getElementById('uploadPreview').innerHTML = `
    <div class="upload-placeholder">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      <span>Нажмите или перетащите фото</span><small>JPG, PNG, WEBP до 5 МБ</small>
    </div>`;
  document.getElementById('photoInput').value = '';
  document.getElementById('uploadStatus').textContent = '';
}

function clearPhoto() {
  currentUploadPath = '';
  document.getElementById('f_image').value = '';
  resetUploadPreview();
}

document.getElementById('uploadZone').addEventListener('click', () => {
  document.getElementById('photoInput').click();
});
document.getElementById('uploadZone').addEventListener('dragover', e => { e.preventDefault(); e.currentTarget.classList.add('drag-over'); });
document.getElementById('uploadZone').addEventListener('dragleave', e => e.currentTarget.classList.remove('drag-over'));
document.getElementById('uploadZone').addEventListener('drop', e => {
  e.preventDefault(); e.currentTarget.classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file) uploadPhoto(file);
});

function handlePhoto(input) {
  if (input.files[0]) uploadPhoto(input.files[0]);
}

async function uploadPhoto(file) {
  const status = document.getElementById('uploadStatus');
  status.innerHTML = '<span class="upload-uploading">⏳ Загружаем...</span>';
  const fd = new FormData();
  fd.append('action', 'upload_image');
  fd.append('photo', file);
  const ci = document.getElementById('f_ci').value;
  fd.append('ci', ci);
  try {
    const res = await fetch('api.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      currentUploadPath = data.path;
      document.getElementById('f_image').value = data.path;
      document.getElementById('uploadPreview').innerHTML =
        `<img src="../${data.path}?v=${Date.now()}" style="width:100%;height:180px;object-fit:cover;border-radius:8px" />
         <button type="button" class="upload-clear" onclick="clearPhoto()">× Удалить фото</button>`;
      status.innerHTML = '<span class="upload-ok">✅ Загружено</span>';
    } else {
      status.innerHTML = `<span class="upload-error">❌ ${data.error}</span>`;
    }
  } catch(e) {
    status.innerHTML = '<span class="upload-error">❌ Ошибка сети</span>';
  }
}

async function saveItem(e) {
  e.preventDefault();
  const btn = document.getElementById('saveBtn');
  btn.disabled = true; btn.textContent = 'Сохраняем...';
  const fd = new FormData(document.getElementById('itemForm'));
  try {
    const res = await fetch('api.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      showToast('✅ Сохранено! Перезагрузите страницу.');
      closeModal();
      setTimeout(() => location.reload(), 800);
    } else {
      showToast('❌ ' + (data.error || 'Ошибка'), 'error');
    }
  } catch(e) {
    showToast('❌ Ошибка сети', 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Сохранить';
  }
}

function updateBadgeText() {
  const map = { popular: '🔥 Хит', exclusive: '✨ Премиум', new: '🆕 Новинка', '': '' };
  document.getElementById('f_badge_text').value = map[document.getElementById('f_badge').value] || '';
}

async function toggleVisible(id, ci, ii) {
  const fd = new FormData();
  fd.append('action', 'toggle_visible'); fd.append('ci', ci); fd.append('ii', ii);
  const res = await fetch('api.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) { showToast(data.visible ? '👁 Показано' : '🙈 Скрыто'); setTimeout(() => location.reload(), 600); }
}

async function deleteItem(id, ci, ii) {
  if (!confirm('Удалить это блюдо навсегда?')) return;
  const fd = new FormData();
  fd.append('action', 'delete_item'); fd.append('ci', ci); fd.append('ii', ii);
  const res = await fetch('api.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) { showToast('🗑 Удалено'); setTimeout(() => location.reload(), 600); }
}

// ---- Drink Modal ----
function openAddDrink(catId, dci) {
  document.getElementById('drinkModalTitle').textContent = 'Добавить напиток';
  document.getElementById('df_cat').value = catId;
  document.getElementById('df_dci').value = dci;
  document.getElementById('df_dii').value = -1;
  document.getElementById('df_name').value = '';
  document.getElementById('df_volume').value = '';
  document.getElementById('df_price').value = '';
  document.getElementById('drinkModal').classList.add('active');
}

function openDrinkEdit(catId, dci, dii) {
  fetch(`api.php?action=get_drink&cat_id=${catId}&dci=${dci}&dii=${dii}`)
    .then(r => r.json()).then(d => {
      document.getElementById('drinkModalTitle').textContent = 'Редактировать напиток';
      document.getElementById('df_cat').value = catId;
      document.getElementById('df_dci').value = dci;
      document.getElementById('df_dii').value = dii;
      document.getElementById('df_name').value = d.name;
      document.getElementById('df_volume').value = d.volume || '';
      document.getElementById('df_price').value = d.price;
      document.getElementById('drinkModal').classList.add('active');
    });
}

function closeDrinkModal() {
  document.getElementById('drinkModal').classList.remove('active');
}

async function saveDrink(e) {
  e.preventDefault();
  const fd = new FormData(document.getElementById('drinkForm'));
  const res = await fetch('api.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) { showToast('✅ Сохранено'); closeDrinkModal(); setTimeout(() => location.reload(), 800); }
  else showToast('❌ ' + (data.error || 'Ошибка'), 'error');
}

async function toggleDrinkVisible(catId, dci, dii) {
  const fd = new FormData();
  fd.append('action', 'toggle_drink_visible');
  fd.append('cat_id', catId); fd.append('dci', dci); fd.append('dii', dii);
  const res = await fetch('api.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) { showToast(data.visible ? '👁 Показано' : '🙈 Скрыто'); setTimeout(() => location.reload(), 600); }
}

// Close modals on backdrop click
document.getElementById('itemModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });
document.getElementById('drinkModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeDrinkModal(); });
</script>
</body>
</html>
