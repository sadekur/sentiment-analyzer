jQuery(function ($) {
    let render_message = (response, msgDivId) => {
        if (response.success == true) {
            const message = response.data.message;

            $(msgDivId).css("color", "#4bc30f").text(message).slideDown();
        } else {
            const message = JSON.parse(response.responseText).data.message;

            $(msgDivId).css("color", "#FF3A52").text(message).slideDown();
        }
    };

    /**
     * Render fragment HTML from the cart response
     */
    let render_fragments = (cart) => {
        $(".easycommerce-cart-wrapper").html(cart.fragments.items);
        $(".easycommerce-summary-wrapper").html(cart.fragments.summary);
        $(".easycommerce-payment-methods").html(cart.fragments.payment_methods);
        const event = new Event("easycommerceRenderPaymentForm");
        document.dispatchEvent(event);
    };

    function updateShippingMethods() {
        let form = $("#easycommerce-checkout");
        let data = form.serializeArray();

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/shipping`,
            type: "GET",
            data: data,
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                render_message(response);
                render_fragments(response.data.cart);
                setTimeout(function () {
                    const checked = $(".easycommerce-shipping-method:checked");
                    if (checked.length) {
                        sendShippingMethod(checked.val());
                    } else {
                        const first = $(".easycommerce-shipping-method").first();
                        if (first.length) {
                            first.prop("checked", true).trigger("change");
                        }
                    }
                }, 100);
            },
            error: function (error) {
                render_message(error);
            },
        });
    }

    $(document).on("change", ".easycommerce-checkout-billing .easycommerce-field", function (e) {
        if ( $( this ).data( "field_id" ) === "country" ) {
            const billingCountry = $( this ).val();
            const allowedShippingCountries = Object.keys( EASYCOMMERCE.shipping.countries );
            const $sameAsShippingCheckbox = $( "#easycommerce-checkout-same-as-shipping" );
            const $sameAsShippingLabel = $( "label:has(#easycommerce-checkout-same-as-shipping)" );

            if ( allowedShippingCountries.length > 0 && ! allowedShippingCountries.includes( billingCountry ) ) {
                $sameAsShippingCheckbox.prop( { checked: false, disabled: true } );
                $sameAsShippingCheckbox.add( $sameAsShippingLabel ).css( {
                    opacity: 0.5,
                    cursor: "not-allowed"
                } );
                $( ".easycommerce-checkout-shipping-same-as-address" ).slideDown();
                return;
            }

            $sameAsShippingCheckbox.prop( "disabled", false );
            $sameAsShippingCheckbox.add( $sameAsShippingLabel ).css( {
                opacity: 1,
                cursor: "pointer"
            } );
        }

        // Copy billing fields to shipping fields if "Same as shipping" is checked
        if ($("#easycommerce-checkout-same-as-shipping").is(":checked")) {
            let val         = $(this).val();
            let field_id    = $(this).data("field_id");
            $(`.easycommerce-checkout-shipping .easycommerce-field[data-field_id="${field_id}"]`).val(val).change();
            updateShippingMethods();
        } else {
            let fieldId = $(this).attr("id");
            if ( fieldId === "easycommerce-field-billing_email" || fieldId === "easycommerce-field-billing_city" ) {
                updateShippingMethods();
            }
        }
    });

    /**
     * Choose a shipping_method for the cart
     */
    function sendShippingMethod(method_id) {
        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/shipping`,
            type: "POST",
            data: { id: method_id },
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                if (response.success === true) {
                    render_fragments(response.data.cart);
                }
            },
            error: function (error) {
                console.error("Shipping update error:", error);
            },
        });
    }
    $(document).on("change", ".easycommerce-shipping-method", function (e) {
        const method_id = $(this).val();
        sendShippingMethod(method_id);
    });
    $(document).ready(function () {
        const defaultShipping = $(".easycommerce-shipping-method:checked");
        if (defaultShipping.length) {
            const method_id = defaultShipping.val();
            sendShippingMethod(method_id);
        }
    });

    $(document).on( "change", "#easycommerce-checkout-same-as-shipping", function (e) {
        if (!$(this).is(":checked")) {
            $( ".easycommerce-checkout-shipping-same-as-address" ).slideDown();
        } else {
            $(".easycommerce-checkout-shipping-same-as-address").slideUp();
        }
    });

    /**
     * When a country is chosen, generate its states
     */
    $('.easycommerce-field[data-field_id="country"]').change(function (e) {
        let country         = $(this).val();
        let wrap            = $(this).closest(".easycommerce-address");
        let address_type    = wrap.data("address_type");

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/geo/states/${country}`,
            type: "GET",
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                if (response.success === true) {
                    let stateHTML = "";
                    $(
                        '.easycommerce-field[data-field_id="state"]',
                        wrap
                    ).html("");

                    // Check if statesData is an object (for key-value pairs)
                    if (
                        typeof response.data.states === "object" &&
                        !Array.isArray(response.data.states)
                    ) {
                        Object.entries(response.data.states).forEach(
                            ([code, state]) => {
                                stateHTML += `<option value="${code}">${state}</option>`;
                            }
                        );
                    }
                    // If statesData is an array (list of state names)
                    else if (Array.isArray(response.data.states)) {
                        response.data.states.forEach((state, index) => {
                            stateHTML += `<option value="${state}">${state}</option>`;
                        });
                    }
                    $('.easycommerce-field[data-field_id="state"]', wrap)
                        .html(stateHTML)
                        .val(EASYCOMMERCE.customer.address[address_type].state)
                        .change();
                }
            },
            error: function (error) {},
        });
    }).change();

    /**
     * When a state is chosen, generate its cities
     * @todo can be further improved to check corresponding data for shipping and billing
     */
    $('.easycommerce-field[data-field_id="state"]').change(function (e) {
        let wrap            = $(this).closest(".easycommerce-address");
        let address_type    = wrap.data("address_type");
        let country         = $(`#easycommerce-field-${address_type}_country`).val();
        let state           = $(this).val();

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/geo/cities/${country}`,
            type: "GET",
            data: { state: state },
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                if (response.success === true) {
                    let cityHTML = "";
                    if (
                        response.data.cities &&
                        ((typeof response.data.cities === "object" &&
                            Object.keys(response.data.cities).length > 0) ||
                            (Array.isArray(response.data.cities) &&
                                response.data.cities.length > 0))
                    ) {
                        // Ensure the city field is a <select> element if options are available
                        if (
                            !$(`#easycommerce-field-${address_type}_city`).is(
                                "select"
                            )
                        ) {
                            $(`#easycommerce-field-${address_type}_city`)
                                .replaceWith(`
                                <select id="easycommerce-field-${address_type}_city" name="${address_type}_address[city]" required class="easycommerce-field" data-field_id="city"></select>
                            `);
                        }

                        // Populate select with options if cities data is not empty
                        if (
                            typeof response.data.cities === "object" &&
                            !Array.isArray(response.data.cities)
                        ) {
                            Object.entries(response.data.cities).forEach(
                                ([code, city]) => {
                                    cityHTML += `<option value="${code}">${city}</option>`;
                                }
                            );
                        } else if (Array.isArray(response.data.cities)) {
                            response.data.cities.forEach((city) => {
                                cityHTML += `<option value="${city}">${city}</option>`;
                            });
                        }
                        $(`#easycommerce-field-${address_type}_city`)
                            .html(cityHTML)
                            .val(EASYCOMMERCE.customer.address[address_type].city)
                            .change();
                    } else {
                        $(`#easycommerce-field-${address_type}_city`)
                            .replaceWith(`
                            <input type="text" id="easycommerce-field-${address_type}_city" name="${address_type}_address[city]" placeholder="Enter city" required class="easycommerce-field" data-field_id="city">
                        `);
                    }
                }
            },
            error: function (error) {},
        });
    });

    /**
     * When shipping city is chosen, get available shipping methods
     * @todo can be further improved to check corresponding data for shipping and billing
     */
    $(document).on("change", "#easycommerce-field-shipping_city", function () {
        updateShippingMethods();
    });

    /**
     * Coupon button apply button visiable
     */
    $(document).on("input", '#easycommerce-coupon-field', function (e) {
        if ($(this).val().trim() !== "") {
            $("#easycommerce-coupon-apply").css({
                color: "#ffffff",
                "background-color": "#272435",
            });
        } else {
            $("#easycommerce-coupon-apply").css({
                color: "#737791",
                "background-color": "#F8F8F8",
            });
        }
    });

    /**
     * Apply a coupon
     */
    $(document).on("click", "#easycommerce-coupon-apply", function (e) {
        let coupon = $("#easycommerce-coupon-field").val();

        if (coupon == "") return;

        $("#easycommerce-checkout-coupon-message").slideUp(); // Hide any previous messages

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/coupon`,
            type: "POST",
            data: { code: coupon },
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (success) {
                render_fragments(success.data.cart);
                $(".easycommerce-discount-wrapper").show();
            },
            error: function (error) {
                render_message(error, "#easycommerce-checkout-coupon-message");
            },
        });
    });

    $(document).on("click", ".easycommerce-remove-coupon", function (e) {
        let coupon = $(this).data("id");

        if (coupon == "") return;

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/coupon/remove`,
            type: "POST",
            data: { code: coupon },
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (success) {
                $(".easycommerce-discount-wrapper").show();
                render_fragments(success.data.cart);
                render_message(success);
            },
            error: function (error) {
                render_message(error);
            },
        });
    });

    /**
     * Place the order
     */
    $("#easycommerce-checkout").on("payment", function (e) {
        e.preventDefault();

        $(".easycommerce-checkout-main-btn").hide();
        $(".easycommerce-css-loader-wrapper").show();

        let form = $(this);
        let data = form.serializeArray();

        let customer = [
            {
                name: "customer[name]",
                value:
                    $("#easycommerce-field-billing_first_name").val() +
                    " " +
                    $("#easycommerce-field-billing_last_name").val(),
            },
            {
                name: "customer[email]",
                value: $("#easycommerce-field-billing_email").val(),
            },
            {
                name: "customer[first_name]",
                value: $("#easycommerce-field-billing_first_name").val(),
            },
            {
                name: "customer[last_name]",
                value: $("#easycommerce-field-billing_last_name").val(),
            },
            {
                name: "customer[meta][phone]",
                value: $("#easycommerce-field-billing_phone").val(),
            },
            {
                name: "customer[meta][country]",
                value: $("#easycommerce-field-billing_country").val(),
            },
        ];

        data.push(...customer);

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/orders`,
            type: "POST",
            data: data,
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                render_message(response);

                if (response.data.redirect != false) {
                    window.location = response.data.redirect;
                }
            },
            error: function (error) {
                render_message(error);
            },
        });
    });

    /**
     * On clicking `minus` button, trigger quantity change
     */
    $(document).on("click", ".easycommerce-checkout-cart-quantity-minus-btn", function(e) {
        let quantity_field = $(this).next(
            ".easycommerce-cart-quantity-input"
        );
        let value = quantity_field.val();
        value > 1 ? value-- : (value = 1);
        quantity_field.val(value);
        quantity_field.trigger("input");
    });

    /**
     * On clicking `plus` button, trigger quantity change
     */
    $(document).on("click", ".easycommerce-checkout-cart-quantity-plus-btn", function(e) {
        let quantity_field      = $(this).prev(".easycommerce-cart-quantity-input");
        let value               = quantity_field.val();
        let maxQuantity         = parseInt(quantity_field.attr("data-stock-count"));
        let type                = quantity_field.attr("data-type");

        if( type == "digital" && value >= 1 ) return;

        if (value >= maxQuantity) return;

        value++;
        quantity_field.val(value);
        quantity_field.trigger("input");
    });

    /**
     * On quantity change, update the cart
     */
    $(document).on("input", ".easycommerce-cart-quantity-input", function(e) {
        let quantity = $(this).val();
        let product_id = $(this).attr("product-id");
        let price_id = $(this).attr("price-id");
        let data = {
            quantity: quantity,
            id: product_id,
            price_id: price_id,
        };

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/update`,
            type: "POST",
            data: data,
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                if (response.success === true) {
                    render_fragments(response.data.cart);
                    $("#easycommerce-field-shipping_city").change();
                }
            },
            error: function (error) {
                render_message(error);
            },
        });
    });

    // Delete Product
    $(document).on("click", ".easycommerce-checkout-cart-item-delete-btn", function(e) {
        let product_id = $(this).attr("product-id");
        let price_id = $(this).attr("price-id");
        let $currentBtn = $(this);
        let data = {
            id: product_id,
            price_id: price_id,
        };

        $.ajax({
            url: `${EASYCOMMERCE.rest_base}/cart/remove`,
            type: "DELETE",
            data: data,
            dataType: "JSON",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", EASYCOMMERCE.nonce);
            },
            success: function (response) {
                $currentBtn.closest(".easycommerce-cart-product").remove();

                if (response.success === true) {
                    let cart = response.data.cart;
                    if (!cart.items?.length) {
                        if (response.data.redirect) {
                            window.location = response.data.redirect;
                        } else if (response.data.reload) {
                            window.location.reload();
                        }
                        return;
                    }
                    render_fragments(cart);
                    if( typeof cart.fragments.shipping_fee == "undefined" ) {
                        $('.easycommerce-shipping-method-wrapper').remove();
                    }
                }
            },

            error: function (error) {
                render_message(error);
            },
        });
    });

    $(document).on("submit", "#easycommerce-checkout",async function(e) {
        e.preventDefault();
        var $method = $(".easycommerce-payment_method:checked").val();

        if ($method === "cod") {
            $(this).trigger("payment");
        }
        //when no payment method exists
        if( $("#easycommerce-payment_methods").length == 0 ) {
            $(this).trigger("payment");
        }
    });

    // Toggle payment methods
    $(document).on("change", ".easycommerce-payment_method",function(e) {
        let method = $(this).val();
        if ($(this).is(":checked")) {
            $(".easycommerce-payment_method-form")
                .not(`#easycommerce-payment_method-${method}-form`)
                .stop(true, true)
                .slideUp();

            $(`#easycommerce-payment_method-${method}-form`)
                .stop(true, true)
                .slideDown();
        }
    });


    const $shippingCheckbox = $("#easycommerce-checkout-same-as-shipping");
    const $shippingInputs = $(".easycommerce-checkout-shipping input, .easycommerce-checkout-shipping select");

    function toggleRequired() {
        if ($shippingCheckbox.is(":checked")) {
            $shippingInputs.removeAttr("required");
        } else {
            $shippingInputs.attr("required", "required");
        }
    }

    toggleRequired();

    $shippingCheckbox.on("change", toggleRequired);

    $(document).on("submit", "#easycommerce-checkout", function (event) {
        let isValid = true;

        if ($(".easycommerce-shipping-method").length === 0 ) {
            return true;
        }
        if (!$(".easycommerce-shipping-method:checked").length) {
            $(".easycommerce-shipping-method-error").removeClass("hidden");
            isValid = false;
        } else {
            $(".easycommerce-shipping-method-error").addClass("hidden");
        }

        if (!$(".easycommerce-payment_method:checked").length) {
            $(".easycommerce-payment-method-error").removeClass("hidden");
            isValid = false;
        } else {
            $(".easycommerce-payment-method-error").addClass("hidden");
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
});
