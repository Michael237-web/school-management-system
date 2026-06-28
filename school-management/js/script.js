// js/script.js
$(document).ready(function() {
    // ============================================
    // TOOLTIPS
    // ============================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // ============================================
    // CONFIRM DELETE
    // ============================================
    $('.delete-confirm').on('click', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (confirm('Are you sure you want to delete this item?')) {
            window.location.href = href;
        }
    });
    
    // ============================================
    // AUTO SUBMIT ON FILTER CHANGE
    // ============================================
    $('.auto-submit').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // ============================================
    // PRINT INVOICE
    // ============================================
    $('.print-invoice').on('click', function() {
        window.print();
    });
    
    // ============================================
    // EXPORT TABLE
    // ============================================
    $('.export-table').on('click', function() {
        var table = $(this).data('table');
        var csv = [];
        var rows = $(table).find('tr');
        
        rows.each(function() {
            var row = [];
            $(this).find('th, td').each(function() {
                row.push($(this).text().trim());
            });
            csv.push(row.join(','));
        });
        
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'export.csv';
        link.click();
    });
    
    // ============================================
    // FEE PAYMENT CALCULATOR
    // ============================================
    $('#payment-amount').on('input', function() {
        var dueAmount = parseFloat($('#due-amount').val()) || 0;
        var paidAmount = parseFloat($(this).val()) || 0;
        
        if (paidAmount > dueAmount) {
            $(this).addClass('is-invalid');
            $('#payment-error').text('Amount cannot exceed due amount');
        } else {
            $(this).removeClass('is-invalid');
            $('#payment-error').text('');
        }
    });
    
    // ============================================
    // ATTENDANCE QUICK MARK
    // ============================================
    $('.quick-attendance').on('click', function(e) {
        e.preventDefault();
        var studentId = $(this).data('student');
        var status = $(this).data('status');
        var date = $(this).data('date');
        
        $.ajax({
            url: 'attendance-mark.php',
            method: 'POST',
            data: {
                student_id: studentId,
                status: status,
                date: date,
                ajax: true
            },
            success: function(response) {
                if (response.success) {
                    $(this).closest('.attendance-item').find('.status-badge')
                        .removeClass('bg-success bg-danger bg-warning bg-info')
                        .addClass('bg-' + response.color)
                        .text(response.status);
                }
            }
        });
    });
    
    // ============================================
    // NOTIFICATION SYSTEM
    // ============================================
    function showNotification(message, type) {
        var colors = {
            success: '#1cc88a',
            error: '#e74a3b',
            warning: '#f6c23e',
            info: '#36b9cc'
        };
        
        var notification = $('<div class="notification">')
            .text(message)
            .css({
                position: 'fixed',
                top: '80px',
                right: '20px',
                padding: '15px 20px',
                backgroundColor: colors[type] || '#4e73df',
                color: '#fff',
                borderRadius: '10px',
                boxShadow: '0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)',
                zIndex: 9999,
                opacity: 0,
                transition: 'opacity 0.3s ease, transform 0.3s ease',
                transform: 'translateX(100px)'
            });
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.css({
                opacity: 1,
                transform: 'translateX(0)'
            });
        }, 100);
        
        setTimeout(function() {
            notification.css({
                opacity: 0,
                transform: 'translateX(100px)'
            });
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 5000);
    }
    
    // ============================================
    // DATE PICKER
    // ============================================
    $('.datepicker').on('focus', function() {
        $(this).attr('type', 'date');
    }).on('blur', function() {
        if (!$(this).val()) {
            $(this).attr('type', 'text');
        }
    });
    
    // ============================================
    // SEARCH WITH DEBOUNCE
    // ============================================
    var debounceTimer;
    $('.search-debounce').on('input', function() {
        clearTimeout(debounceTimer);
        var self = $(this);
        debounceTimer = setTimeout(function() {
            self.closest('form').submit();
        }, 500);
    });
});