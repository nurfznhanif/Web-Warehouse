        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan konfirmasi hapus
        function confirmDelete(message) {
            return confirm(message || 'Apakah Anda yakin ingin menghapus data ini?');
        }

        // Fungsi untuk menambahkan row dinamis pada form
        function addRow(containerId, templateFn) {
            const container = document.getElementById(containerId);
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 md:grid-cols-12 gap-4 items-end';
            newRow.innerHTML = templateFn();
            container.appendChild(newRow);
        }

        // Fungsi untuk menghapus row
        function removeRow(button) {
            button.closest('.grid.grid-cols-1').remove();
        }
    </script>
</body>
</html>