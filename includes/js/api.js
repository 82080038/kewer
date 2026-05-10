/**
 * Global API Helper for Kewer Application
 * Centralized API calls with error handling and response standardization
 */

(function($) {
    'use strict';

    // API Base URL
    const API_BASE = '/api';

    // API Helper Class
    class KewerAPI {
        constructor() {
            this.baseUrl = API_BASE;
        }

        // Generic GET request
        async get(endpoint, params = {}) {
            try {
                const response = await $.ajax({
                    url: this.baseUrl + endpoint,
                    method: 'GET',
                    data: params,
                    dataType: 'json'
                });
                return this.handleResponse(response);
            } catch (error) {
                return this.handleError(error);
            }
        }

        // Generic POST request
        async post(endpoint, data = {}) {
            try {
                const response = await $.ajax({
                    url: this.baseUrl + endpoint,
                    method: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    dataType: 'json'
                });
                return this.handleResponse(response);
            } catch (error) {
                return this.handleError(error);
            }
        }

        // Generic PUT request
        async put(endpoint, data = {}) {
            try {
                const response = await $.ajax({
                    url: this.baseUrl + endpoint,
                    method: 'PUT',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    dataType: 'json'
                });
                return this.handleResponse(response);
            } catch (error) {
                return this.handleError(error);
            }
        }

        // Generic DELETE request
        async delete(endpoint, params = {}) {
            try {
                const response = await $.ajax({
                    url: this.baseUrl + endpoint,
                    method: 'DELETE',
                    data: params,
                    dataType: 'json'
                });
                return this.handleResponse(response);
            } catch (error) {
                return this.handleError(error);
            }
        }

        // Handle successful response
        handleResponse(response) {
            if (response.success === false) {
                throw new Error(response.error || 'Unknown error');
            }
            return response;
        }

        // Handle error
        handleError(error) {
            console.error('API Error:', error);
            const message = error.responseJSON?.error || error.message || 'Terjadi kesalahan sistem';
            return { success: false, error: message };
        }

        // ============ NASABAH API ============
        async getNasabah(params = {}) {
            return this.get('/nasabah.php', params);
        }

        async getNasabahDetail(id) {
            return this.get('/nasabah.php', { id: id });
        }

        async createNasabah(data) {
            return this.post('/nasabah.php', data);
        }

        async updateNasabah(id, data) {
            return this.put('/nasabah.php?id=' + id, data);
        }

        async deleteNasabah(id) {
            return this.delete('/nasabah.php', { id: id });
        }

        // ============ PINJAMAN API ============
        async getPinjaman(params = {}) {
            return this.get('/pinjaman.php', params);
        }

        async getPinjamanDetail(id) {
            return this.get('/pinjaman.php', { id: id });
        }

        async createPinjaman(data) {
            return this.post('/pinjaman.php', data);
        }

        async updatePinjaman(id, data) {
            return this.put('/pinjaman.php?id=' + id, data);
        }

        async approvePinjaman(id) {
            return this.put('/pinjaman.php', { id: id, action: 'approve' });
        }

        async rejectPinjaman(id) {
            return this.put('/pinjaman.php', { id: id, action: 'reject' });
        }

        async deletePinjaman(id) {
            return this.delete('/pinjaman.php', { id: id });
        }

        // ============ ANGSURAN API ============
        async getAngsuran(params = {}) {
            return this.get('/angsuran.php', params);
        }

        async getAngsuranDetail(id) {
            return this.get('/angsuran.php', { id: id });
        }

        async getAngsuranByPinjaman(pinjamanId) {
            return this.get('/angsuran.php', { pinjaman_id: pinjamanId });
        }

        // ============ PEMBAYARAN API ============
        async getPembayaran(params = {}) {
            return this.get('/pembayaran.php', params);
        }

        async getPembayaranDetail(id) {
            return this.get('/pembayaran.php', { id: id });
        }

        async createPembayaran(data) {
            return this.post('/pembayaran.php', data);
        }

        async updatePembayaran(id, data) {
            return this.put('/pembayaran.php?id=' + id, data);
        }

        // ============ CABANG API ============
        async getCabang(params = {}) {
            return this.get('/cabang.php', params);
        }

        async createCabang(data) {
            return this.post('/cabang.php', data);
        }

        async updateCabang(id, data) {
            return this.put('/cabang.php?id=' + id, data);
        }

        async deleteCabang(id) {
            return this.delete('/cabang.php', { id: id });
        }

        // ============ PETUGAS API ============
        async getPetugas(params = {}) {
            return this.get('/users.php', params);
        }

        async getPetugasDetail(id) {
            return this.get('/users.php', { id: id });
        }

        async createPetugas(data) {
            return this.post('/users.php', data);
        }

        async updatePetugas(id, data) {
            return this.put('/users.php?id=' + id, data);
        }

        async deletePetugas(id) {
            return this.delete('/users.php', { id: id });
        }

        // ============ DASHBOARD API ============
        async getDashboardStats() {
            return this.get('/dashboard.php', { action: 'stats' });
        }

        async getDashboardCharts() {
            return this.get('/dashboard.php', { action: 'charts' });
        }

        async getDashboardRecent() {
            return this.get('/dashboard.php', { action: 'recent' });
        }

        // ============ LAPORAN API ============
        async getLaporanKeuangan(params = {}) {
            return this.get('/export.php', { type: 'keuangan', ...params });
        }

        async getLaporanPinjaman(params = {}) {
            return this.get('/export.php', { type: 'pinjaman', ...params });
        }

        async getLaporanNasabah(params = {}) {
            return this.get('/export.php', { type: 'nasabah', ...params });
        }

        // ============ KAS PETUGAS API ============
        async getKasPetugas(params = {}) {
            return this.get('/kas_petugas.php', params);
        }

        async createSetoran(data) {
            return this.post('/kas_petugas_setoran.php', data);
        }

        // ============ PENGELUARAN API ============
        async getPengeluaran(params = {}) {
            return this.get('/pengeluaran.php', params);
        }

        async createPengeluaran(data) {
            return this.post('/pengeluaran.php', data);
        }

        async updatePengeluaran(id, data) {
            return this.put('/pengeluaran.php?id=' + id, data);
        }

        async deletePengeluaran(id) {
            return this.delete('/pengeluaran.php', { id: id });
        }

        // ============ NOTIFICATIONS API ============
        async getNotifications(params = {}) {
            return this.get('/notifications.php', params);
        }

        async getNotificationCount(status = 'sent') {
            return this.get('/notifications.php', { action: 'count', status: status });
        }

        async markNotificationAsRead(id) {
            return this.post('/notifications.php', { action: 'mark_read', id: id });
        }

        async markAllNotificationsAsRead(status = 'sent') {
            return this.post('/notifications.php', { action: 'mark_all_read', status: status });
        }

        // ============ AUTH API ============
        async login(username, password) {
            return this.post('/auth.php', { action: 'login', username, password });
        }

        async logout() {
            return this.post('/auth.php', { action: 'logout' });
        }

        async getCurrentUser() {
            return this.get('/auth.php', { action: 'me' });
        }

        // ============ SETTINGS API ============
        async getSettings() {
            return this.get('/feature_flags.php');
        }

        async updateSetting(key, value) {
            return this.put('/feature_flags.php', { key, value });
        }
    }

    // Create global instance
    window.KewerAPI = new KewerAPI();

    // jQuery plugin for data fetching
    $.fn.loadData = function(endpoint, params = {}) {
        const $el = $(this);
        $el.html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
        
        window.KewerAPI.get(endpoint, params).done(response => {
            $el.trigger('data:loaded', response);
        }).fail(error => {
            $el.html('<div class="alert alert-danger">Gagal memuat data</div>');
            $el.trigger('data:error', error);
        });
        
        return this;
    };

    // DataTables AJAX source helper
    $.fn.ajaxDataTable = function(options) {
        const settings = $.extend({
            ajax: {
                url: window.KewerAPI.baseUrl + options.endpoint,
                type: 'GET',
                data: options.params || {},
                dataSrc: function(json) {
                    return json.data || json || [];
                }
            },
            columns: options.columns || []
        }, options);

        $(this).DataTable(settings);
        return this;
    };

})(jQuery);
