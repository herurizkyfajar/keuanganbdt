<?php
// chart-of-accounts.php
require_once 'includes/header.php';

// Handle POST actions BEFORE any HTML output
$message = '';
$saved = false;
$updated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_account') {
    $code = $_POST['code'] ?? '';
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $parent = $_POST['parent_id'] ?: null;
    $desc = $_POST['description'] ?? '';
    try {
        $stmt = $pdo->prepare("INSERT INTO accounts (business_id, code, name, type, parent_id, description) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$businessId, $code, $name, $type, $parent, $desc]);
        header('Location: chart-of-accounts.php?saved=1');
        exit;
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Update account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_account') {
    $id = (int)($_POST['id'] ?? 0);
    $code = $_POST['code'] ?? '';
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $parent = $_POST['parent_id'] ?: null;
    $desc = $_POST['description'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    try {
        $stmt = $pdo->prepare("UPDATE accounts SET code=?, name=?, type=?, parent_id=?, description=?, is_active=? WHERE id=? AND business_id=?");
        $stmt->execute([$code, $name, $type, $parent, $desc, $is_active, $id, $businessId]);
        header('Location: chart-of-accounts.php?updated=1');
        exit;
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Delete account (always soft delete - set is_active = 0, never hard delete)
// Data akun tidak pernah dihancurkan permanen, sehingga selalu dapat dikembalikan.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        $message = '<div class="alert alert-danger">ID akun tidak valid.</div>';
    } else {
        // Check if account has transaction lines (untuk pesan informatif saja)
        $chk = $pdo->prepare("SELECT COUNT(*) as cnt FROM transaction_lines WHERE account_id = ?");
        $chk->execute([$id]);
        $cnt = $chk->fetchColumn();
        
        // Selalu soft delete untuk mencegah kehilangan data permanen
        try {
            $stmt = $pdo->prepare("UPDATE accounts SET is_active = 0 WHERE id = ? AND business_id = ?");
            $stmt->execute([$id, $businessId]);
            if ($cnt > 0) {
                $message = '<div class="alert alert-success">Akun berhasil dinonaktifkan (sudah memiliki transaksi, tidak dapat dihapus permanen).</div>';
            } else {
                $message = '<div class="alert alert-success">Akun berhasil dinonaktifkan (dapat dikembalikan kapan saja).</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        header('Location: chart-of-accounts.php');
        exit;
    }
}

// Reorder account (move up / down) via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder_accounts') {
    $id = (int)($_POST['id'] ?? 0);
    $direction = $_POST['direction'] ?? '';
    if ($id > 0 && in_array($direction, ['up', 'down'])) {
        try {
            $pdo->beginTransaction();
            $cur = $pdo->prepare("SELECT sort_order FROM accounts WHERE id = ? AND business_id = ?");
            $cur->execute([$id, $businessId]);
            $currentSort = $cur->fetchColumn();
            if ($currentSort !== false) {
                if ($direction === 'up') {
                    $other = $pdo->prepare("SELECT id, sort_order FROM accounts WHERE business_id = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1");
                    $other->execute([$businessId, $currentSort]);
                } else {
                    $other = $pdo->prepare("SELECT id, sort_order FROM accounts WHERE business_id = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1");
                    $other->execute([$businessId, $currentSort]);
                }
                $target = $other->fetch();
                if ($target) {
                    $pdo->prepare("UPDATE accounts SET sort_order = ? WHERE id = ? AND business_id = ?")->execute([$target['sort_order'], $id, $businessId]);
                    $pdo->prepare("UPDATE accounts SET sort_order = ? WHERE id = ? AND business_id = ?")->execute([$currentSort, $target['id'], $businessId]);
                }
            }
            $pdo->commit();
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            ob_clean();
            header('Content-Type: application/json', true, 500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        ob_clean();
        header('Content-Type: application/json', true, 400);
        echo json_encode(['success' => false, 'error' => 'Permintaan tidak valid.']);
    }
    exit;
}

// Add new account group (parent account)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_group') {
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $desc = $_POST['description'] ?? '';
    if (empty($name)) {
        $message = '<div class="alert alert-danger">Nama grup harus diisi.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO accounts (business_id, code, name, type, parent_id, description, is_active) VALUES (?, ?, ?, ?, NULL, ?, 1)");
            $stmt->execute([$businessId, $name, $name, $type, $desc]);
            header('Location: chart-of-accounts.php?saved=1');
            exit;
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Edit existing account group (parent account)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_group') {
    $id = (int)($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $desc = $_POST['description'] ?? '';
    if ($id <= 0) {
        $message = '<div class="alert alert-danger">ID grup tidak valid.</div>';
    } elseif (empty($name)) {
        $message = '<div class="alert alert-danger">Nama grup harus diisi.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE accounts SET name=?, code=?, type=?, description=? WHERE id=? AND business_id=? AND parent_id IS NULL");
            $stmt->execute([$name, $name, $type, $desc, $id, $businessId]);
            header('Location: chart-of-accounts.php?updated=1');
            exit;
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Add new account under a group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_account') {
    $code = $_POST['code'] ?? '';
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $parent = $_POST['parent_id'] ?: null;
    $desc = $_POST['description'] ?? '';
    try {
        $stmt = $pdo->prepare("INSERT INTO accounts (business_id, code, name, type, parent_id, description, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$businessId, $code, $name, $type, $parent, $desc]);
        header('Location: chart-of-accounts.php?saved=1');
        exit;
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Gagal: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

$saved = isset($_GET['saved']);
$updated = isset($_GET['updated']);
$reordered = isset($_GET['moved']);
$reorderedId = $_GET['moved'] ?? null;

// Ensure sort_order column exists
try {
    $pdo->query("ALTER TABLE accounts ADD COLUMN sort_order INT DEFAULT 0 AFTER parent_id");
} catch (PDOException $e) {
    // Column likely already exists
}

// Initialize sort_order for existing accounts based on code order (only if all are 0)
$maxSort = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM accounts WHERE business_id = ?");
$maxSort->execute([$businessId]);
if ((int)$maxSort->fetchColumn() === 0) {
    $list = $pdo->prepare("SELECT id FROM accounts WHERE business_id = ? ORDER BY code ASC");
    $list->execute([$businessId]);
    $upd = $pdo->prepare("UPDATE accounts SET sort_order = ? WHERE id = ? AND business_id = ?");
    $i = 1;
    foreach ($list->fetchAll() as $row) {
        $upd->execute([$i, $row['id'], $businessId]);
        $i++;
    }
}

// Fetch accounts
$stmt = $pdo->prepare("
  SELECT a.id, a.code, a.name, a.type, a.sort_order, a.parent_id, a.description, a.is_active,
         COUNT(l.id) as has_lines,
         COALESCE(SUM(
           CASE 
             WHEN a.type IN ('asset','bank','expense') THEN l.debit - l.credit
             WHEN a.type IN ('liability','equity','revenue') THEN l.credit - l.debit
             ELSE 0
           END
         ), 0) AS balance
  FROM accounts a
  LEFT JOIN transaction_lines l ON l.account_id = a.id
  LEFT JOIN transactions t ON t.id = l.transaction_id AND t.business_id = ?
  WHERE a.business_id = ? AND a.is_active = 1
  GROUP BY a.id
  ORDER BY a.sort_order ASC, a.code ASC
");
$stmt->execute([$businessId, $businessId]);
$allAccounts = $stmt->fetchAll();

// Split into Balance Sheet and Profit & Loss groups
$bsAccounts = [];
$plAccounts = [];
foreach ($allAccounts as $a) {
    if (in_array($a['type'], ['asset', 'bank', 'liability', 'equity'])) {
        $bsAccounts[] = $a;
    } else {
        $plAccounts[] = $a;
    }
}

// Group ordering for Balance Sheet
$bsGroupOrder = [
    'asset'   => 'Assets',
    'bank'    => 'Assets',
    'liability' => 'Liabilities',
    'equity'  => 'Equity',
];

// Group ordering for P&L
$plGroupOrder = [
    'revenue' => 'Pemasukkan',
    'expense' => 'Less: Beban',
];

function renderStatementPanel(string $title, array $buttons, array $accounts, array $groupOrder): string {
    $html = '';
    $html .= '<div class="statement-panel">';
    $html .= '<div class="statement-header">';
    $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
    $html .= '<div class="statement-actions">';
    foreach ($buttons as $btn) {
        $html .= '<button class="btn btn-sm btn-primary">' . htmlspecialchars($btn) . '</button>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<table class="statement-table">';
    $html .= '<thead><tr><th style="width:44px;"></th><th>Name</th><th style="width:140px;"></th><th style="width:44px;"></th></tr></thead>';
    $html .= '<tbody>';

    $typeToGroup = $groupOrder;
    if (!isset($typeToGroup['bank'])) {
        $typeToGroup['bank'] = 'Assets';
    }

    $groupIndex = array_flip($groupOrder);
    // Urutkan terutama menurut judul grup agar setiap judul (mis. "Pemasukkan" /
    // "Less: Beban") muncul sekali saja dengan semua akun anggotanya di bawahnya
    // (efek GROUP BY judul grup). Dalam satu grup, urutan manual (sort_order)
    // dan kode akun tetap dipertahankan.
    usort($accounts, function ($a, $b) use ($groupIndex, $typeToGroup) {
        $ga = $typeToGroup[$a['type']] ?? 'Lainnya';
        $gb = $typeToGroup[$b['type']] ?? 'Lainnya';
        $ia = $groupIndex[$ga] ?? PHP_INT_MAX;
        $ib = $groupIndex[$gb] ?? PHP_INT_MAX;
        if ($ia !== $ib) return $ia <=> $ib; // judul grup utama
        $sa = (int)($a['sort_order'] ?? 0);
        $sb = (int)($b['sort_order'] ?? 0);
        if ($sa !== $sb) return $sa <=> $sb; // urutan manual
        return ($a['code'] ?? '') <=> ($b['code'] ?? ''); // kode akun
    });

    // Map tipe -> nama grup asli dari database (account induk parent_id=NULL)
    $groupNameFromDb = [];
    foreach ($accounts as $ga) {
        if (($ga['parent_id'] ?? null) === null) {
            $gt = $typeToGroup[$ga['type']] ?? 'Lainnya';
            if (!isset($groupNameFromDb[$gt])) {
                $groupNameFromDb[$gt] = $ga['name'];
            }
        }
    }

    $currentGroup = null;
    foreach ($accounts as $a) {
        $group = $typeToGroup[$a['type']] ?? 'Lainnya';

        if ($group !== $currentGroup) {
            if ($currentGroup !== null) {
                $html .= '</tbody><tbody>';
            }
            // Cari account grup (induk) untuk tombol Edit Grup: cocokkan tipe
            // dan parent_id IS NULL, sehingga grup yang sudah diedit namanya
            // tetap muncul tombol editenya.
            $g = null;
            foreach ($accounts as $ga) {
                if (($ga['parent_id'] ?? null) === null
                    && ($ga['type'] ?? '') === $a['type']) {
                    $g = $ga;
                    break;
                }
            }
            $editBtn = $g ? '<button class="btn btn-sm btn-outline" style="float:right;" onclick="editGroup('.htmlspecialchars(json_encode($g, ENT_QUOTES), ENT_QUOTES).')" title="Edit grup"><i class="bi bi-pencil"></i></button>' : '';
            $groupLabel = $groupNameFromDb[$group] ?? $group;
            $html .= '<tr class="group-row"><td colspan="3">' . htmlspecialchars($groupLabel) . ' ' . $editBtn . '</td></tr>';
            $currentGroup = $group;
        }

        $isCredit = in_array($a['type'], ['liability', 'equity', 'revenue']);
        $html .= '<tr>';
        $html .= '<td><div style="display:flex; gap:0.25rem; align-items:center;">';
        $html .= '<button class="btn btn-sm btn-outline" onclick="editAccount('.htmlspecialchars(json_encode($a, ENT_QUOTES), ENT_QUOTES).')"><i class="bi bi-pencil"></i></button>';
        $html .= '<button class="btn btn-sm btn-outline" style="color:#dc3545;border-color:#dc3545;" onclick="deleteAccount('.htmlspecialchars(json_encode($a, ENT_QUOTES), ENT_QUOTES).')" title="Hapus akun"><i class="bi bi-trash"></i></button>';
        $html .= '</div></td>';
        $html .= '<td>' . htmlspecialchars($a['code'] . ' - ' . $a['name']) . '</td>';
        $html .= '<td style="text-align:right;">';
        if ($isCredit) {
            $html .= '<span class="text-credit">' . number_format($a['balance'], 0, ',', '.') . '</span>';
        } else {
            $html .= '<span class="text-debit">' . number_format($a['balance'], 0, ',', '.') . '</span>';
        }
        $html .= '</td>';
        $html .= '<td style="text-align:center;">';
        $html .= '<button class="btn btn-sm btn-outline sort-up" data-account-id="' . $a['id'] . '" onclick="reorderAccount('.htmlspecialchars($a['id']).', \'up\')" title="Naikkan urutan"><i class="bi bi-arrow-up-short"></i></button>';
        $html .= '<button class="btn btn-sm btn-outline sort-down" data-account-id="' . $a['id'] . '" onclick="reorderAccount('.htmlspecialchars($a['id']).', \'down\')" title="Turunkan urutan"><i class="bi bi-arrow-down-short"></i></button>';
        $html .= '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';

    return $html;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($businessName) ?> - Manajer Akuntansi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
.statement-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-top: 1rem;
}
.statement-panel {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
}
.statement-header {
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  padding: 0.85rem 1.1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.statement-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #333;
}
.statement-actions {
  display: flex;
  gap: 0.5rem;
}
.statement-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
  table-layout: auto;
}
.statement-table th {
  background: #f8f9fa;
  color: #6c757d;
  font-weight: 600;
  text-align: left;
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e9ecef;
}
.statement-table td {
  padding: 0.5rem 1rem;
  border-bottom: 1px solid #f1f3f5;
  white-space: normal;
  overflow: visible;
  text-overflow: unset;
}
.statement-table tr.group-row {
  background: #f1f3f5;
  font-weight: 700;
  color: #495057;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.statement-table tr.group-row td {
  padding: 0.5rem 1rem;
  border-bottom: none;
}
.text-debit { color: #055160; }
.text-credit { color: #0f5132; }
.drag-handles {
  font-size: 0.7rem;
  color: #adb5bd;
  user-select: none;
}
.btn-outline {
  background: transparent;
  border: 1px solid #ced4da;
  color: #495057;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  cursor: pointer;
}
.btn-outline:hover {
  background: #f8f9fa;
  border-color: #adb5bd;
}
.btn.sort-up, .btn.sort-down {
  padding: 0.15rem 0.35rem;
  font-size: 0.65rem;
  margin: 0 0.1rem;
  min-width: 2.2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.btn.sort-up i, .btn.sort-down i {
  font-size: 0.7rem;
}
tr.highlight-moved {
  background: #fff3cd !important;
  transition: background 1.5s ease;
}
</style>
<script>
document.getElementById('pageTitle').textContent = 'Daftar Akun';
document.getElementById('pageSubtitle').textContent = 'Daftar rekening akuntansi perusahaan';
</script>

      <?php echo $message; ?>
      <?php if ($saved): ?><div class="alert alert-success">Akun berhasil ditambahkan.</div><?php endif; ?>
      <?php if ($updated): ?><div class="alert alert-success">Akun berhasil diperbarui.</div><?php endif; ?>
      <?php if ($reordered): ?><div class="alert alert-success">Urutan akun berhasil diperbarui.</div><?php endif; ?>

      <div class="section">
        <div class="section-header"><i class="bi bi-journal-text"></i> Daftar Akun</div>
        <div class="section-body p-0">
          <div class="statement-columns">
            <?= renderStatementPanel('Balance Sheet', ['New Group', 'New Account'], $bsAccounts, $bsGroupOrder) ?>
            <?= renderStatementPanel('Profit and Loss Statement', ['New Group', 'New Account', 'New Total'], $plAccounts, $plGroupOrder) ?>
          </div>
        </div>
      </div>

      <!-- Modal Edit Akun -->
      <div class="modal-overlay" id="modalEditAccount">
        <div class="modal modal-modern" style="max-width:560px;">
          <form method="POST" action="">
            <input type="hidden" name="action" value="update_account">
            <input type="hidden" id="editAccountId" name="id">
            <div class="mdlg-header">
              <svg class="mdlg-deco mdlg-deco-top" viewBox="0 0 600 140" preserveAspectRatio="none" aria-hidden="true">
                <path d="M170,140 C300,80 430,30 600,4" stroke="rgba(255,255,255,0.9)" stroke-width="26" fill="none" stroke-linecap="round"/>
                <path d="M235,140 C360,92 470,48 600,30" stroke="#8dc63f" stroke-width="17" fill="none" stroke-linecap="round"/>
                <path d="M305,140 C425,110 515,74 600,62" stroke="rgba(255,255,255,0.45)" stroke-width="9" fill="none" stroke-linecap="round"/>
              </svg>
              <div class="mdlg-header-icon"><i class="bi bi-pencil-square"></i></div>
              <div class="mdlg-header-text">
                <div class="mdlg-header-title">Edit Akun</div>
                <div class="mdlg-header-sub">Perbarui data akun</div>
              </div>
              <button type="button" class="mdlg-close" onclick="closeModal('modalEditAccount')" aria-label="Tutup">&times;</button>
            </div>
            <div class="mdlg-body">
              <div class="form-grid">
                <div>
                  <label class="mdlg-label" for="editCode"><i class="bi bi-hash"></i> Kode Akun <span class="req">*</span></label>
                  <input type="text" id="editCode" name="code" class="mdlg-input" required>
                </div>
                <div>
                  <label class="mdlg-label" for="editName"><i class="bi bi-fonts"></i> Nama Akun <span class="req">*</span></label>
                  <input type="text" id="editName" name="name" class="mdlg-input" required>
                </div>
                <div class="form-col-2">
                  <label class="mdlg-label" for="editType"><i class="bi bi-layers"></i> Tipe Akun <span class="req">*</span></label>
                  <select id="editType" name="type" class="mdlg-input" required>
                    <option value="asset">Aset</option>
                    <option value="bank">Bank</option>
                    <option value="liability">Kewajiban</option>
                    <option value="equity">Ekuitas</option>
                    <option value="revenue">Pendapatan</option>
                    <option value="expense">Beban</option>
                  </select>
                </div>
                <div>
                  <label class="mdlg-label" for="editParentId"><i class="bi bi-diagram-2"></i> Akun Induk</label>
                  <select id="editParentId" name="parent_id" class="mdlg-input">
                    <option value="">(Akun Utama)</option>
                    <?php foreach ($allAccounts as $a): ?>
                      <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-col-2">
                  <label class="mdlg-label" for="editDescription"><i class="bi bi-journal-text"></i> Deskripsi</label>
                  <input type="text" id="editDescription" name="description" class="mdlg-input" placeholder="Opsional">
                </div>
                <div class="form-col-2">
                  <label class="mdlg-label" for="editIsActive" style="margin-bottom:0;">
                    <input type="checkbox" id="editIsActive" name="is_active" value="1" checked>
                    Akun aktif
                  </label>
                </div>
              </div>
            </div>
            <div class="mdlg-footer">
              <svg class="mdlg-deco mdlg-deco-bottom" viewBox="0 0 420 90" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,72 C95,42 215,36 335,56 L420,68 L420,90 L0,90 Z" fill="rgba(141,198,63,0.18)"/>
                <path d="M0,84 C115,64 255,62 420,80 L420,90 L0,90 Z" fill="rgba(33,110,215,0.10)"/>
              </svg>
              <button type="button" class="mdlg-btn-cancel" onclick="closeModal('modalEditAccount')">Batal</button>
              <button type="submit" class="mdlg-btn-save"><i class="bi bi-save"></i> Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>

    </div>

<script>
function openModal(id){ document.getElementById(id).classList.add('show'); }
function closeModal(id){ document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{ m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('show'); }); });

function editAccount(data) {
  document.getElementById('editAccountId').value = data.id;
  document.getElementById('editCode').value = data.code;
  document.getElementById('editName').value = data.name;
  document.getElementById('editType').value = data.type;
  document.getElementById('editParentId').value = data.parent_id || '';
  document.getElementById('editDescription').value = data.description || '';
  document.getElementById('editIsActive').checked = data.is_active == 1;
  openModal('modalEditAccount');
}

function deleteAccount(data) {
  if (confirm('Yakin ingin menghapus akun "' + data.name + '"?\n\nAkun yang sudah memiliki transaksi akan dinonaktifkan (tidak dapat dihapus permanen).')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    const inputAction = document.createElement('input');
    inputAction.type = 'hidden';
    inputAction.name = 'action';
    inputAction.value = 'delete_account';
    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id';
    inputId.value = data.id;
    form.appendChild(inputAction);
    form.appendChild(inputId);
    document.body.appendChild(form);
    form.submit();
  }
}

function reorderAccount(id, direction) {
  console.log('reorderAccount called:', id, direction);
  fetch(window.location.href, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=reorder_accounts&id=' + id + '&direction=' + direction
  })
  .then(function(r) {
    console.log('reorder response status:', r.status);
    return r.json();
  })
  .then(function(data) {
    console.log('reorder response data:', data);
    if (data.success) {
      // Reload dengan parameter untuk menandai baris yang dipindahkan
      const url = new URL(window.location.href);
      url.searchParams.set('moved', id);
      url.searchParams.delete('saved');
      url.searchParams.delete('updated');
      window.location.href = url.toString();
    } else {
      alert('Gagal mengurutkan: ' + (data.error || 'tidak diketahui'));
    }
  })
  .catch(function(err) {
    console.error('reorder fetch error:', err);
    alert('Gagal mengurutkan akun.');
  });
}
</script>
<script>
<?php if ($reordered && $reorderedId): ?>
document.addEventListener('DOMContentLoaded', function() {
  var btn = document.querySelector('button[data-account-id="<?php echo (int)$reorderedId; ?>"]');
  if (btn) {
    var row = btn.closest('tr');
    if (row) {
      row.classList.add('highlight-moved');
      setTimeout(function(){ row.classList.remove('highlight-moved'); }, 1500);
    }
  }
});
<?php endif; ?>
</script>
<script>
// Tombol New Group dan New Account di panel statement
document.querySelectorAll('.statement-actions .btn-primary').forEach(btn => {
  btn.addEventListener('click', function() {
    const text = this.textContent.trim();
    if (text === 'New Group') {
      document.getElementById('modalGroupName').value = '';
      document.getElementById('modalGroupType').value = 'asset';
      openModal('modalAddGroup');
    } else if (text === 'New Account') {
      document.getElementById('modalAddAccountForm').reset();
      document.getElementById('modalAddAccountCode').value = '';
      document.getElementById('modalAddAccountName').value = '';
      document.getElementById('modalAddAccountType').value = '';
      document.getElementById('modalAddAccountParent').value = '';
      document.getElementById('modalAddAccountDesc').value = '';
      openModal('modalAddAccount');
    } else if (text === 'New Total') {
      alert('Fitur Total akan segera tersedia.');
    }
  });
});

function editGroup(data) {
  document.getElementById('editGroupId').value = data.id;
  document.getElementById('editGroupName').value = data.name;
  document.getElementById('editGroupType').value = data.type;
  document.getElementById('editGroupDesc').value = data.description || '';
  openModal('modalEditGroup');
}
</script>
<!-- Modal Edit Grup Akun -->
<div class="modal-overlay" id="modalEditGroup">
  <div class="modal modal-modern" style="max-width:560px;">
    <form method="POST" action="">
      <input type="hidden" name="action" value="edit_group">
      <input type="hidden" id="editGroupId" name="id">
      <div class="mdlg-header">
        <svg class="mdlg-deco mdlg-deco-top" viewBox="0 0 600 140" preserveAspectRatio="none" aria-hidden="true">
          <path d="M170,140 C300,80 430,30 600,4" stroke="rgba(255,255,255,0.9)" stroke-width="26" fill="none" stroke-linecap="round"/>
          <path d="M235,140 C360,92 470,48 600,30" stroke="#8dc63f" stroke-width="17" fill="none" stroke-linecap="round"/>
          <path d="M305,140 C425,110 515,74 600,62" stroke="rgba(255,255,255,0.45)" stroke-width="9" fill="none" stroke-linecap="round"/>
        </svg>
        <div class="mdlg-header-icon"><i class="bi bi-folder2-open"></i></div>
        <div class="mdlg-header-text">
          <div class="mdlg-header-title">Edit Grup Akun</div>
          <div class="mdlg-header-sub">Perbarui data grup akun</div>
        </div>
        <button type="button" class="mdlg-close" onclick="closeModal('modalEditGroup')" aria-label="Tutup">&times;</button>
      </div>
      <div class="mdlg-body">
        <div class="form-grid">
          <div class="form-col-2">
            <label class="mdlg-label" for="editGroupName"><i class="bi bi-folder"></i> Nama Grup <span class="req">*</span></label>
            <input type="text" id="editGroupName" name="name" class="mdlg-input" placeholder="Contoh: Aset Tetap" required>
          </div>
          <div>
            <label class="mdlg-label" for="editGroupType"><i class="bi bi-layers"></i> Tipe Grup <span class="req">*</span></label>
            <select id="editGroupType" name="type" class="mdlg-input" required>
              <option value="asset">Aset</option>
              <option value="bank">Bank</option>
              <option value="liability">Kewajiban</option>
              <option value="equity">Ekuitas</option>
              <option value="revenue">Pendapatan</option>
              <option value="expense">Beban</option>
            </select>
          </div>
          <div class="form-col-2">
            <label class="mdlg-label" for="editGroupDesc"><i class="bi bi-journal-text"></i> Deskripsi</label>
            <input type="text" id="editGroupDesc" name="description" class="mdlg-input" placeholder="Opsional">
          </div>
        </div>
      </div>
      <div class="mdlg-footer">
        <svg class="mdlg-deco mdlg-deco-bottom" viewBox="0 0 420 90" preserveAspectRatio="none" aria-hidden="true">
          <path d="M0,72 C95,42 215,36 335,56 L420,68 L420,90 L0,90 Z" fill="rgba(141,198,63,0.18)"/>
          <path d="M0,84 C115,64 255,62 420,80 L420,90 L0,90 Z" fill="rgba(33,110,215,0.10)"/>
        </svg>
        <button type="button" class="mdlg-btn-cancel" onclick="closeModal('modalEditGroup')">Batal</button>
        <button type="submit" class="mdlg-btn-save"><i class="bi bi-save"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
<!-- Modal Tambah Grup Akun -->
<div class="modal-overlay" id="modalAddGroup">
  <div class="modal modal-modern" style="max-width:560px;">
    <form method="POST" action="">
      <input type="hidden" name="action" value="add_group">
      <div class="mdlg-header">
        <svg class="mdlg-deco mdlg-deco-top" viewBox="0 0 600 140" preserveAspectRatio="none" aria-hidden="true">
          <path d="M170,140 C300,80 430,30 600,4" stroke="rgba(255,255,255,0.9)" stroke-width="26" fill="none" stroke-linecap="round"/>
          <path d="M235,140 C360,92 470,48 600,30" stroke="#8dc63f" stroke-width="17" fill="none" stroke-linecap="round"/>
          <path d="M305,140 C425,110 515,74 600,62" stroke="rgba(255,255,255,0.45)" stroke-width="9" fill="none" stroke-linecap="round"/>
        </svg>
        <div class="mdlg-header-icon"><i class="bi bi-folder-plus"></i></div>
        <div class="mdlg-header-text">
          <div class="mdlg-header-title">Tambah Grup Akun</div>
          <div class="mdlg-header-sub">Buat grup akun baru</div>
        </div>
        <button type="button" class="mdlg-close" onclick="closeModal('modalAddGroup')" aria-label="Tutup">&times;</button>
      </div>
      <div class="mdlg-body">
        <div class="form-grid">
          <div class="form-col-2">
            <label class="mdlg-label" for="modalGroupName"><i class="bi bi-folder"></i> Nama Grup <span class="req">*</span></label>
            <input type="text" id="modalGroupName" name="name" class="mdlg-input" placeholder="Contoh: Aset Tetap" required>
          </div>
          <div>
            <label class="mdlg-label" for="modalGroupType"><i class="bi bi-layers"></i> Tipe Grup <span class="req">*</span></label>
            <select id="modalGroupType" name="type" class="mdlg-input" required>
              <option value="asset">Aset</option>
              <option value="bank">Bank</option>
              <option value="liability">Kewajiban</option>
              <option value="equity">Ekuitas</option>
              <option value="revenue">Pendapatan</option>
              <option value="expense">Beban</option>
            </select>
          </div>
          <div class="form-col-2">
            <label class="mdlg-label"><i class="bi bi-journal-text"></i> Deskripsi</label>
            <input type="text" name="description" class="mdlg-input" placeholder="Opsional">
          </div>
        </div>
      </div>
      <div class="mdlg-footer">
        <svg class="mdlg-deco mdlg-deco-bottom" viewBox="0 0 420 90" preserveAspectRatio="none" aria-hidden="true">
          <path d="M0,72 C95,42 215,36 335,56 L420,68 L420,90 L0,90 Z" fill="rgba(141,198,63,0.18)"/>
          <path d="M0,84 C115,64 255,62 420,80 L420,90 L0,90 Z" fill="rgba(33,110,215,0.10)"/>
        </svg>
        <button type="button" class="mdlg-btn-cancel" onclick="closeModal('modalAddGroup')">Batal</button>
        <button type="submit" class="mdlg-btn-save"><i class="bi bi-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Tambah Akun -->
<div class="modal-overlay" id="modalAddAccount">
  <div class="modal modal-modern" style="max-width:560px;">
    <form method="POST" action="" id="modalAddAccountForm">
      <input type="hidden" name="action" value="add_account">
      <div class="mdlg-header">
        <svg class="mdlg-deco mdlg-deco-top" viewBox="0 0 600 140" preserveAspectRatio="none" aria-hidden="true">
          <path d="M170,140 C300,80 430,30 600,4" stroke="rgba(255,255,255,0.9)" stroke-width="26" fill="none" stroke-linecap="round"/>
          <path d="M235,140 C360,92 470,48 600,30" stroke="#8dc63f" stroke-width="17" fill="none" stroke-linecap="round"/>
          <path d="M305,140 C425,110 515,74 600,62" stroke="rgba(255,255,255,0.45)" stroke-width="9" fill="none" stroke-linecap="round"/>
        </svg>
        <div class="mdlg-header-icon"><i class="bi bi-journal-plus"></i></div>
        <div class="mdlg-header-text">
          <div class="mdlg-header-title">Tambah Akun</div>
          <div class="mdlg-header-sub">Buat akun baru di bagan akun</div>
        </div>
        <button type="button" class="mdlg-close" onclick="closeModal('modalAddAccount')" aria-label="Tutup">&times;</button>
      </div>
      <div class="mdlg-body">
        <div class="form-grid">
          <div>
            <label class="mdlg-label" for="modalAddAccountCode"><i class="bi bi-hash"></i> Kode Akun <span class="req">*</span></label>
            <input type="text" id="modalAddAccountCode" name="code" class="mdlg-input" placeholder="Contoh: 110" required>
          </div>
          <div>
            <label class="mdlg-label" for="modalAddAccountName"><i class="bi bi-fonts"></i> Nama Akun <span class="req">*</span></label>
            <input type="text" id="modalAddAccountName" name="name" class="mdlg-input" placeholder="Contoh: Kas" required>
          </div>
          <div class="form-col-2">
            <label class="mdlg-label" for="modalAddAccountType"><i class="bi bi-layers"></i> Tipe Akun <span class="req">*</span></label>
            <select id="modalAddAccountType" name="type" class="mdlg-input" required>
              <option value="">Pilih tipe akun</option>
              <option value="asset">Aset</option>
              <option value="bank">Bank</option>
              <option value="liability">Kewajiban</option>
              <option value="equity">Ekuitas</option>
              <option value="revenue">Pendapatan</option>
              <option value="expense">Beban</option>
            </select>
          </div>
          <div>
            <label class="mdlg-label" for="modalAddAccountParent"><i class="bi bi-diagram-2"></i> Akun Induk</label>
            <select id="modalAddAccountParent" name="parent_id" class="mdlg-input">
              <option value="">(Akun Utama)</option>
              <?php foreach ($allAccounts as $a): ?>
                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['code'] . ' - ' . $a['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-col-2">
            <label class="mdlg-label" for="modalAddAccountDesc"><i class="bi bi-journal-text"></i> Deskripsi</label>
            <input type="text" id="modalAddAccountDesc" name="description" class="mdlg-input" placeholder="Opsional">
          </div>
        </div>
      </div>
      <div class="mdlg-footer">
        <svg class="mdlg-deco mdlg-deco-bottom" viewBox="0 0 420 90" preserveAspectRatio="none" aria-hidden="true">
          <path d="M0,72 C95,42 215,36 335,56 L420,68 L420,90 L0,90 Z" fill="rgba(141,198,63,0.18)"/>
          <path d="M0,84 C115,64 255,62 420,80 L420,90 L0,90 Z" fill="rgba(33,110,215,0.10)"/>
        </svg>
        <button type="button" class="mdlg-btn-cancel" onclick="closeModal('modalAddAccount')">Batal</button>
        <button type="submit" class="mdlg-btn-save"><i class="bi bi-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
