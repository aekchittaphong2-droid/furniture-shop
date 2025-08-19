$(document).ready(function() {
    // ອັບເດດຈຳນວນສິນຄ້າໃນຕະກ້າ
    updateCartCount();
    
    // ເພີ່ມສິນຄ້າເຂົ້າຕະກ້າ (ໃນໜ້າລາຍລະອຽດ)
    $('.add-to-cart').click(function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var quantity = $('#quantity').val() || 1;
        
        addToCart(productId, quantity);
    });
    
    // ເພີ່ມສິນຄ້າແບບດ່ວນ (ໃນໜ້າສິນຄ້າ)
    $(document).on('click', '.add-to-cart-quick', function() {
        var productId = $(this).data('product-id');
        addToCart(productId, 1);
    });
    
    // ຟັງຊັນເພີ່ມສິນຄ້າເຂົ້າຕະກ້າ
    function addToCart(productId, quantity) {
        $.ajax({
            url: getBasePath() + 'ajax/add_to_cart.php',
            type: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                    if(data.success) {
                        showAlert('ເພີ່ມສິນຄ້າເຂົ້າຕະກ້າສຳເລັດ!', 'success');
                        updateCartCount();
                    } else {
                        if(data.message.includes('ເຂົ້າສູ່ລະບົບ')) {
                            if(confirm('ກະລຸນາເຂົ້າສູ່ລະບົບກ່ອນ. ຕ້ອງການໄປໜ້າເຂົ້າສູ່ລະບົບບໍ່?')) {
                                window.location.href = getBasePath() + 'user/login.php';
                            }
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    }
                } catch(e) {
                                            showAlert('ເກີດຂໍ້ຜິດພາດ', 'danger');
                }
            },
            error: function() {
                showAlert('ບໍ່ສາມາດເຊື່ອມຕໍ່ກັບເຊີເວີໄດ້', 'danger');
            }
        });
    }
    
    // ອັບເດດຈຳນວນສິນຄ້າໃນຕະກ້າ
    function updateCartCount() {
        $.get(getBasePath() + 'ajax/get_cart_count.php', function(count) {
            $('.cart-count').text(count);
        });
    }
    
    // ລຶບສິນຄ້າອອກຈາກຕະກ້າ
    $('.remove-from-cart').click(function() {
        var cartId = $(this).data('cart-id');
        var row = $(this).closest('tr');
        
        if(confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບສິນຄ້ານີ້?')) {
            $.ajax({
                url: getBasePath() + 'ajax/remove_from_cart.php',
                type: 'POST',
                data: { cart_id: cartId },
                success: function(response) {
                    var data = JSON.parse(response);
                    if(data.success) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            updateCartTotal();
                            updateCartCount();
                        });
                    }
                }
            });
        }
    });
    
    // ອັບເດດຍອດລວມຕະກ້າ
    function updateCartTotal() {
        var total = 0;
        $('.item-total').each(function() {
            total += parseFloat($(this).data('price'));
        });
        $('#cart-total').text(formatPrice(total));
    }
    
    // ຟັງຊັນຈັດຮູບແບບລາຄາ
    function formatPrice(price) {
        return new Intl.NumberFormat('lo-LA').format(price) + ' ກີບ';
    }
    
    // ແສດງ Alert
    function showAlert(message, type) {
        var alert = $('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>');
        
        // ເພີ່ມໄປເທິງສຸດຂອງ container
        if($('.container').length) {
            $('.container').first().prepend(alert);
        } else {
            $('body').prepend(alert);
        }
        
        // ເລື່ອນໄປເທິງສຸດ
        $('html, body').animate({ scrollTop: 0 }, 'fast');
        
        // ຊ່ອນອັດຕະໂນມັດ
        setTimeout(function() {
            alert.fadeOut();
        }, 3000);
    }
    
    // ການຊອກຫາສິນຄ້າ
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.product-card').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // ການກັ່ນຕອງລາຄາ
    $('#priceRange').on('input', function() {
        var maxPrice = $(this).val();
        $('#priceValue').text(formatPrice(maxPrice));
        
        $('.product-card').each(function() {
            var price = parseFloat($(this).data('price'));
            if(price <= maxPrice) {
                $(this).parent().show();
            } else {
                $(this).parent().hide();
            }
        });
    });
    
    // Form Validation
    $('form.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Image Preview
    $('#image').change(function() {
        var file = this.files[0];
        if (file) {
            // ตรวจสอบขนาดไฟล์ (5MB)
            if (file.size > 5000000) {
                alert('ไฟล์ใหญ่เกินไป กรุณาเลือกไฟล์ที่มีขนาดไม่เกิน 5MB');
                $(this).val('');
                $('#imagePreview').html('');
                return;
            }
            
            // ตรวจสอบประเภทไฟล์
            var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('กรุณาเลือกไฟล์รูปภาพ (JPG, PNG, GIF)');
                $(this).val('');
                $('#imagePreview').html('');
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').html('<img src="' + e.target.result + '" class="img-thumbnail" width="200">');
            }
            reader.readAsDataURL(file);
        } else {
            $('#imagePreview').html('');
        }
    });
    
    // ຟັງຊັນຊ່ວຍໃນການຫາ base path
    function getBasePath() {
        var path = window.location.pathname;
        if(path.includes('/user/') || path.includes('/admin/')) {
            return '../';
        }
        return '';
    }
});