$(document).ready(function() {
    let cart = [];

    $('#productsSelect').select2({ placeholder: "Search for a product", allowClear: true });

    $('#productsSelect').on('change', function() {
        var productId = $(this).val();
        if (productId) {
            var opt = $(this).find('option:selected');
            var name = opt.data('name');
            var price = parseFloat(opt.data('price'));
            var stock = parseInt(opt.data('stock'));

            let existing = cart.find(item => item.id == productId);
            if (existing) {
                if (existing.quantity < stock) existing.quantity++;
                else showMessage('Stock Limit', 'You cannot add more than available stock.');
            } else {
                cart.push({ id: productId, name: name, price: price, quantity: 1, available_stock: stock });
            }
            updateCartDisplay();
            $('#productsSelect').val('').trigger('change.select2');
        }
    });

    $(document).on('click', '.remove-item', function() {
        var productId = $(this).data('id');
        cart = cart.filter(i => i.id != productId);
        updateCartDisplay();
    });

    $(document).on('change', '.item-quantity', function() {
        var productId = $(this).data('id');
        var newQty = parseInt($(this).val());
        let item = cart.find(i => i.id == productId);
        if (item) {
            if (newQty > 0 && newQty <= item.available_stock) item.quantity = newQty;
            else {
                showMessage('Invalid Quantity', 'Quantity exceeds stock.');
                $(this).val(item.quantity);
            }
            updateCartDisplay();
        }
    });

    function updateCartDisplay() {
        let total = 0;
        let tbody = $('#cartTable tbody');
        tbody.empty();

        if (cart.length === 0) {
            $('#processSaleBtn').prop('disabled', true);
            tbody.append('<tr><td colspan="5" class="text-center">No items in the cart.</td></tr>');
        } else {
            $('#processSaleBtn').prop('disabled', false);
            cart.forEach(item => {
                let itemTotal = item.price * item.quantity;
                total += itemTotal;
                tbody.append(`
                    <tr>
                        <td>${item.name}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td><input type="number" class="form-control item-quantity" data-id="${item.id}" value="${item.quantity}" min="1"></td>
                        <td>$${itemTotal.toFixed(2)}</td>
                        <td><button type="button" class="btn btn-sm btn-danger remove-item" data-id="${item.id}">Remove</button></td>
                    </tr>
                `);
            });
        }

        $('#total_amount').val(total.toFixed(2));
        $('#cartItemsInput').val(JSON.stringify(cart));
    }

    function showMessage(title, body) {
        var modal = new bootstrap.Modal(document.getElementById('messageModal'));
        $('#modalTitle').text(title);
        $('#modalBody').text(body);
        modal.show();
    }
});