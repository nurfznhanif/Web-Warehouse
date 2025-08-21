<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Vendor</h1>
            <p class="text-gray-600 mt-2">Kelola data vendor dan supplier</p>
        </div>
        <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Tambah Vendor</span>
        </button>
    </div>

    <!-- Statistics Cards -->
    <?php if (isset($statistics)): ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Vendor</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($statistics['total_vendors']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Vendor Aktif</p>
                        <p class="text-2xl font-bold text-green-600"><?= number_format($statistics['vendors_with_purchases']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Vendor Tidak Aktif</p>
                        <p class="text-2xl font-bold text-gray-500"><?= number_format($statistics['inactive_vendors']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-pause-circle text-gray-500"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Top Vendor</p>
                        <p class="text-lg font-bold text-purple-600"><?= $statistics['top_vendor']['name'] ?? 'Belum Ada' ?></p>
                        <?php if (isset($statistics['top_vendor']['total_amount'])): ?>
                            <p class="text-sm text-gray-500">Rp <?= number_format($statistics['top_vendor']['total_amount'], 0, ',', '.') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <form method="GET" action="<?= base_url('/vendors') ?>" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari Vendor</label>
                <input type="text" id="search" name="search" value="<?= esc($search ?? '') ?>"
                    placeholder="Cari berdasarkan nama, alamat, telepon, atau email..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200 flex items-center space-x-2">
                    <i class="fas fa-search"></i>
                    <span>Cari</span>
                </button>
                <?php if (!empty($search)): ?>
                    <a href="<?= base_url('/vendors') ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Reset</span>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Vendors Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Vendor</h3>
            <?php if (isset($total_items)): ?>
                <p class="text-sm text-gray-600">Total: <?= number_format($total_items) ?> vendor</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($vendors)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Vendor
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kontak
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statistik Pembelian
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal Dibuat
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($vendors as $vendor): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-truck text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= esc($vendor['name']) ?></div>
                                            <?php if (!empty($vendor['address'])): ?>
                                                <div class="text-sm text-gray-500 max-w-xs truncate"><?= esc($vendor['address']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php if (!empty($vendor['phone'])): ?>
                                            <div class="flex items-center mb-1">
                                                <i class="fas fa-phone text-gray-400 mr-2"></i>
                                                <?= esc($vendor['phone']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($vendor['email'])): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                                <a href="mailto:<?= esc($vendor['email']) ?>" class="text-blue-600 hover:text-blue-800"><?= esc($vendor['email']) ?></a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div class="flex items-center mb-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?= number_format($vendor['purchase_count']) ?> Pembelian
                                            </span>
                                        </div>
                                        <?php if ($vendor['pending_purchases'] > 0): ?>
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <?= number_format($vendor['pending_purchases']) ?> Pending
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y', strtotime($vendor['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick="openEditModal(<?= $vendor['id'] ?>, '<?= esc($vendor['name']) ?>', '<?= esc($vendor['address']) ?>', '<?= esc($vendor['phone']) ?>', '<?= esc($vendor['email']) ?>')"
                                            class="text-indigo-600 hover:text-indigo-900 transition duration-200" title="Edit Vendor">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($vendor['purchase_count'] == 0): ?>
                                            <a href="<?= base_url('/vendors/delete/' . $vendor['id']) ?>"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus vendor <?= esc($vendor['name']) ?>?')"
                                                class="text-red-600 hover:text-red-900 transition duration-200" title="Hapus Vendor">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400" title="Tidak dapat menghapus vendor yang memiliki transaksi">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (isset($pager)): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <?= $pager ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-truck text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Vendor</h3>
                <p class="text-gray-500 mb-6">Mulai dengan menambahkan vendor pertama Anda.</p>
                <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Tambah Vendor
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tambah Vendor Baru</h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="createForm" action="<?= base_url('/vendors/store') ?>" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="createName" class="block text-sm font-medium text-gray-700 mb-1">Nama Vendor <span class="text-red-500">*</span></label>
                        <input type="text" id="createName" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan nama vendor">
                    </div>

                    <div>
                        <label for="createAddress" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea id="createAddress" name="address" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan alamat vendor"></textarea>
                    </div>

                    <div>
                        <label for="createPhone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" id="createPhone" name="phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan nomor telepon">
                    </div>

                    <div>
                        <label for="createEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="createEmail" name="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan email vendor">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeCreateModal()"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Vendor</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="editForm" method="POST">
                <input type="hidden" id="editId" name="id">

                <div class="space-y-4">
                    <div>
                        <label for="editName" class="block text-sm font-medium text-gray-700 mb-1">Nama Vendor <span class="text-red-500">*</span></label>
                        <input type="text" id="editName" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="editAddress" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea id="editAddress" name="address" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label for="editPhone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" id="editPhone" name="phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="editEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="editEmail" name="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700">
                        <i class="fas fa-save mr-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.getElementById('createName').focus();
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.getElementById('createForm').reset();
    }

    // Edit Modal Functions
    function openEditModal(id, name, address, phone, email) {
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editAddress').value = address;
        document.getElementById('editPhone').value = phone;
        document.getElementById('editEmail').value = email;
        document.getElementById('editForm').action = '<?= base_url('/vendors/update/') ?>' + id;
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editName').focus();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editForm').reset();
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const createModal = document.getElementById('createModal');
        const editModal = document.getElementById('editModal');

        if (event.target === createModal) {
            closeCreateModal();
        }
        if (event.target === editModal) {
            closeEditModal();
        }
    }

    // Handle Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>
<?= $this->endSection() ?>