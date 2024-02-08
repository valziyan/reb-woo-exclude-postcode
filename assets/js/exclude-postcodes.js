document.addEventListener('DOMContentLoaded', function() {

    console.log('exclude-postcodes');

    // Listen for the checkout updated event
    wp.hooks.addAction('woocommerce_checkout_updated', 'checkoutUpdated', function() {
        // Get the current checkout state
        const checkoutState = wp.data.select('core/checkout').getCheckout();

        // Get the shipping address from the checkout state
        const shippingAddress = checkoutState.shipping.address;

        // Check if the shipping postcode is excluded
        if (excludedPostcodesData.excludedPostcodes.includes(shippingAddress.postcode)) {
            // Prevent checkout from proceeding further
            wp.data.dispatch('core/notices').createNotice('error', 'Sorry, ordering is not available for your location.', {
                id: 'postcode-exclusion-error',
            });

            // Optionally, you can clear the shipping address to prevent further submission
            wp.data.dispatch('core/checkout').updateShippingAddress({
                address: {},
            });
        }
    });
});
