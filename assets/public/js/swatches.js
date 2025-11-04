jQuery(function ($) {
    $(document).ready(function () {
        // Loop through all attribute wrappers
        $('.easycommerce-attributes-wrapper').each(function () {
            var variationsData = $(this).data('variations');
            if (!variationsData) return;

            // Check if any variation is digital
            var hasDigital = variationsData.some(function (variation) {
                return variation.type === 'digital';
            });

            if (hasDigital) {
                $('.easycommerce-add-to-cart').hide();
            } else {
                $('.easycommerce-add-to-cart').show();
            }
        });

        if ($("#easycommerce_variation_id").val() === "1") {
            $(".easycommerce-add-to-cart-button").prop("disabled", true);
        }
        
        $(document).on("change", '.easycommerce-attributes-wrapper input[type="checkbox"]', function () {
            var form            = $(this).closest(".easycommerce-attributes-wrapper");
            var variationObj    = form.data("variations");
            var variations      = variationObj;
            var attributeKeys   = [];

            if (variations.length > 0 && variations[0].attributes && Array.isArray(variations[0].attributes)) {
                var uniqueAttributes = {};
                variations[0].attributes.forEach(function(attr) {
                    if (attr.attribute_slug && !uniqueAttributes[attr.attribute_slug]) {
                        uniqueAttributes[attr.attribute_slug] = true;
                        attributeKeys.push(attr.attribute_slug);
                    }
                });
            }
            
            var matchValArray       = [];
            var matchAttrArray      = [];
            var finalAllMatch       = [];
            var alreadyMatchedAttr  = [];
            let sliceIndex          = 0;
            
            $('.easycommerce-qunatity-input').val(1);
            $(this).closest(".easycommerce-vs-wrapper").find('input[type="checkbox"]').not(this).prop("checked", false);

            attributeKeys.forEach((attribute) => {
                var checkedBoxes = $(`input[name="attribute_${attribute}"]:checked`);
                checkedBoxes.each(function () {
                    var attrValue = $(this).val();
                    var attrName = attribute;
                    if (attrValue) {
                        matchValArray.push(attrValue);
                    }
                    if (attrName) {
                        matchAttrArray.push(attrName);
                    }
                });
            });

            function attributesToObject(attributesArray) {
                var obj = {};
                if (Array.isArray(attributesArray)) {
                    attributesArray.forEach(function(attr) {
                        if (attr.attribute_slug && attr.value_slug) {
                            obj[attr.attribute_slug] = attr.value_slug;
                        }
                    });
                }
                return obj;
            }

            Object.entries(attributeKeys).forEach(() => {
                const compAttr = matchAttrArray[sliceIndex];
                const compVal = matchValArray.slice(0, sliceIndex).concat(matchValArray.slice(sliceIndex + 1));

                if (compAttr != undefined) {
                    variations.forEach((variation) => {
                        var attributes = attributesToObject(variation.attributes);
                        if (compVal.every((val) => Object.values(attributes).includes(val))) {
                            if (!finalAllMatch[compAttr]) {
                                finalAllMatch[compAttr] = [];
                                alreadyMatchedAttr.push(compAttr);
                            }
                            if (!finalAllMatch[compAttr].includes(attributes[compAttr])) {
                                finalAllMatch[compAttr].push(attributes[compAttr]);
                            }
                        }
                    });
                }
                sliceIndex++;
            });

            var missingAttr = attributeKeys.filter(
                (key) => !alreadyMatchedAttr.includes(key)
            );
            if (missingAttr.length) {
                missingAttr.forEach((missingKey) => {
                    variations.forEach((variation) => {
                        var attributes = attributesToObject(variation.attributes);
                        if (matchValArray.every((val) => Object.values(attributes).includes(val))) {
                            if (!finalAllMatch[missingKey]) {
                                finalAllMatch[missingKey] = [];
                            }
                            if (!finalAllMatch[missingKey].includes(attributes[missingKey])) {
                                finalAllMatch[missingKey].push(attributes[missingKey]);
                            }
                        }
                    });
                });
            }

            // Disable options based on matching attributes
            attributeKeys.forEach((attrName) => {
                var options = form.find(`input[name="attribute_${attrName}"]`);
                options.each(function () {
                    var termName = $(this).val();
                    var shouldDisable = finalAllMatch[attrName] && !finalAllMatch[attrName].includes(termName);

                    $(this).prop("disabled", shouldDisable);

                    if (matchValArray.includes(termName)) {
                        $(this).prop("checked", true);
                    }
                });
            });

            // Check if all attributes are selected
            if (matchValArray.length === attributeKeys.length) {
                variations.forEach((variation) => {
                    var attributes = attributesToObject(variation.attributes);
                    if (matchValArray.every((val) => Object.values(attributes).includes(val))) {
                        var variationId = variation.id;
                        var productId = variation.price_id;
                        var price = variation.price;
                        var sale_price = variation.sale_price;
                        var stock_count = variation.stock_count;
                        var type = variation.type;
                        
                        var matchingSlide = $(".swiper-slide").filter(function () {
                            var dataId = $(this).attr("data-id");
                            try {
                                var idsArray = JSON.parse(dataId.replace(/&quot;/g, '"'));
                                return idsArray.includes(String(variationId));
                            } catch (e) {
                                return false;
                            }
                        });

                        $("#easycommerce_variation_id").val(variationId);
                        $("#easycommerce_variation_price_id").val(productId);
                        $(".easycommerce-add-to-cart-button").prop("disabled", false);
                        $(".easycommerce-product-price").text(price);
                        $(".easycommerce-product-sale-price").text(sale_price);
                        $(".easycommerce-stock-count").text(stock_count);
                        $(".easycommerce-qunatity-input").attr("data-stock-count", stock_count);
                        $(".easycommerce-qunatity-input").attr("data-type", type);
                        
                        if (matchingSlide.length) {
                            $(".swiper-slide").removeClass("swiper-slide-active");
                            matchingSlide.first().addClass("swiper-slide-active");

                            const swiperInstance = $(".swiper").get(0)?.swiper;
                            if (swiperInstance) {
                                swiperInstance.slideTo(matchingSlide.first().index());
                            }
                        }
                        
                        if (stock_count == null) {
                            $(".easycommerce-stock-count-wrapper").hide();
                        } else {
                            $(".easycommerce-stock-count-wrapper").show();
                        }

                        if (stock_count == 0) {
                            $(".easycommerce-add-to-cart-button").prop("disabled", true);
                        } else {
                            $(".easycommerce-add-to-cart-button").prop("disabled", false);
                        }
                    }
                });
            } else {
                $('.easycommerce-qunatity-input').val(1);
                $("#easycommerce_variation_id").val(1);
                $("#easycommerce_variation_price_id").val(1);
                $(".easycommerce-add-to-cart-button").prop("disabled", true);
                $(".easycommerce-stock-count").text(0);
                $(".easycommerce-qunatity-input").attr("data-stock-count", 0);
            }
        });
    });
});