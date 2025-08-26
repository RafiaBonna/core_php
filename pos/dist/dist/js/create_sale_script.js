$(document).ready(function() {
    // Custom function to show the modal message box
    function showMessage(title, message) {
        // Since you are using a modal, let's make sure its style is correct
        // For example:
        $('#modalTitle').text(title);
        $('#modalMessage').text(message);
        $('#messageModal').css('display', 'flex');
    }

    // Custom function to hide the modal message box
    $('#closeModalBtn').on('click', function() {
        $('#messageModal').css('display', 'none');
    });

    // Initialize Select2 for search functionality on dropdowns.
    $('.select2').select2({
        placeholder: "Start typing to search...",
        allowClear: true
    });

    // An empty array to hold our cart items.
    let cart = [];

    // Add to Cart functionality.
    $('#addToCart').on('click', function() {
        var productId = $('#product_id').val();
        var quantity = parseInt($('#quantity').val());

        // Client-side validation.
        if (!productId || quantity <= 0) {
            showMessage('Validation Error', 'Please select a product and enter a valid quantity.');
            return;
        }

        var product = $('#product_id option:selected');
        var productName = product.text().split('-')[0].trim();
        var productPrice = parseFloat(product.data('price'));
        var availableStock = parseInt(product.data('stock'));

        if (quantity > availableStock) {
            showMessage('Stock Error', 'The quantity exceeds the available stock.');
            return;
        }

        // Check if the item is already in the cart.
        let existingItem = cart.find(item => item.id == productId);
        if (existingItem) {
            // Update quantity if item exists.
            let newQuantity = existingItem.quantity + quantity;
            if (newQuantity > availableStock) {
                showMessage('Stock Error', 'Adding this quantity exceeds available stock.');
                return;
            }
            existingItem.quantity = newQuantity;
        } else {
            // Add new item to the cart.
            cart.push({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: quantity
            });
        }
        
        updateCartDisplay();
        // Reset the product and quantity fields.
        $('#product_id').val(null).trigger('change');
        $('#quantity').val(1);
    });

    // Handle removing an item from the cart.
    $(document).on('click', '.remove-item', function() {
        var productId = $(this).data('id');
        cart = cart.filter(item => item.id != productId);
        updateCartDisplay();
    });

    // Handle quantity change directly in the cart table.
    $(document).on('change', '.item-quantity', function() {
        var productId = $(this).data('id');
        var newQuantity = parseInt($(this).val());
        var item = cart.find(item => item.id == productId);
        
        var productOption = $(`#product_id option[value='${productId}']`);
        var availableStock = parseInt(productOption.data('stock'));

        if (newQuantity > 0 && newQuantity <= availableStock) {
            item.quantity = newQuantity;
        } else {
            showMessage('Invalid Quantity', 'Invalid quantity or exceeds available stock.');
            $(this).val(item.quantity); // Revert to old value.
        }
        updateCartDisplay();
    });

    // This function updates the cart table and total amount.
    function updateCartDisplay() {
        let totalAmount = 0;
        let cartTableBody = $('#cartTable tbody');
        cartTableBody.empty(); // Clear the table body.

        if (cart.length > 0) {
            $('#processSaleBtn').prop('disabled', false);
        } else {
            $('#processSaleBtn').prop('disabled', true);
        }
        
        cart.forEach(item => {
            let itemTotal = item.price * item.quantity;
            totalAmount += itemTotal;
            let row = `
                <tr>
                    <td>${item.name}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td><input type="number" class="form-control item-quantity" data-id="${item.id}" value="${item.quantity}" min="1"></td>
                    <td>$${itemTotal.toFixed(2)}</td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-item" data-id="${item.id}">Remove</button></td>
                </tr>
            `;
            cartTableBody.append(row);
        });

        $('#total_amount').val(totalAmount.toFixed(2));
        $('#cartItemsInput').val(JSON.stringify(cart)); // Update hidden input with JSON.
    }
});
