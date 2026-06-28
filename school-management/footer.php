<?php
// footer.php
?>
        </div> <!-- End page-content -->
    </div> <!-- End page-wrapper -->
    
    <!-- ============================================ -->
    <!-- JAVASCRIPT - Loaded at the bottom for better performance -->
    <!-- ============================================ -->
    <!-- jQuery (Must load first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    
    <script>
    // ============================================
    // PAGE INITIALIZATION
    // ============================================
    $(document).ready(function() {
        // Initialize DataTables
        $('.datatable').DataTable({
            pageLength: <?php echo ITEMS_PER_PAGE ?? 15; ?>,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).alert('close');
            });
        }, 5000);
        
        // ============================================
        // MOBILE DROPDOWN FIX - Keep dropdowns open when clicking inside
        // ============================================
        $('.dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });
        
        // ============================================
        // CLOSE MOBILE MENU ON LINK CLICK
        // ============================================
        $('.navbar-nav .nav-link:not(.dropdown-toggle)').on('click', function() {
            var $navbarCollapse = $('#navbarNav');
            if ($navbarCollapse.hasClass('show')) {
                $navbarCollapse.collapse('hide');
            }
        });
        
        // Close mobile menu when dropdown items are clicked
        $('.dropdown-item').on('click', function() {
            var $navbarCollapse = $('#navbarNav');
            if ($navbarCollapse.hasClass('show')) {
                $navbarCollapse.collapse('hide');
            }
        });
        
        // ============================================
        // HAMBURGER ICON ANIMATION
        // ============================================
        $('.navbar-toggler').on('click', function() {
            $(this).toggleClass('active');
        });
        
        // ============================================
        // MOBILE DROPDOWN TOGGLE - Prevent navigation on dropdown toggle click
        // ============================================
        $('.dropdown-toggle').on('click', function(e) {
            if ($(window).width() <= 991) {
                // Check if this is the user dropdown on mobile
                var $this = $(this);
                var $dropdownMenu = $this.next('.dropdown-menu');
                
                // If it's the user dropdown on mobile, allow it
                if ($this.closest('.user-dropdown-mobile').length) {
                    e.preventDefault();
                    // Toggle the dropdown
                    if ($dropdownMenu.hasClass('show')) {
                        $dropdownMenu.removeClass('show');
                        $this.attr('aria-expanded', 'false');
                    } else {
                        // Close all other dropdowns first
                        $('.dropdown-menu').not($dropdownMenu).removeClass('show');
                        $('.dropdown-toggle').not($this).attr('aria-expanded', 'false');
                        $dropdownMenu.addClass('show');
                        $this.attr('aria-expanded', 'true');
                    }
                } else if ($this.closest('.dropdown').length) {
                    // For other dropdowns
                    e.preventDefault();
                    // Close all other dropdowns first
                    $('.dropdown-menu').not($dropdownMenu).removeClass('show');
                    $('.dropdown-toggle').not($this).attr('aria-expanded', 'false');
                    
                    // Toggle this dropdown
                    $dropdownMenu.toggleClass('show');
                    var isExpanded = $dropdownMenu.hasClass('show');
                    $this.attr('aria-expanded', isExpanded);
                }
            }
        });
        
        // ============================================
        // FIX: Close dropdown when clicking outside
        // ============================================
        $(document).on('click', function(e) {
            if ($(window).width() <= 991) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').removeClass('show');
                    $('.dropdown-toggle').attr('aria-expanded', 'false');
                }
            }
        });
        
        // ============================================
        // USER DROPDOWN - Ensure it works on mobile
        // ============================================
        $('.user-dropdown-mobile .dropdown-toggle').on('click', function(e) {
            if ($(window).width() <= 991) {
                e.preventDefault();
                var $dropdownMenu = $(this).next('.dropdown-menu');
                if ($dropdownMenu.hasClass('show')) {
                    $dropdownMenu.removeClass('show');
                    $(this).attr('aria-expanded', 'false');
                } else {
                    $dropdownMenu.addClass('show');
                    $(this).attr('aria-expanded', 'true');
                }
            }
        });
    });
    </script>
</body>
</html>