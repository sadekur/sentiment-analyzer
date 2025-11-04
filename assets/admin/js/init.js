const currentHash = window.location.hash;

Object.keys(EASYCOMMERCE.admin.menus).forEach((key) => {
    const subMenuItems = document.querySelectorAll(
        `.toplevel_page_${key} .wp-submenu.wp-submenu-wrap > li`
    );

    subMenuItems.forEach(function (item) {
        const link = item.querySelector("a");

        // Add .current on-click
        item.addEventListener("click", function () {
            subMenuItems.forEach((li) => li.classList.remove("current"));
            item.classList.add("current");
        });

        // Add .current to current menu based on hash
        if ( link && link.hash && (currentHash.includes(link.hash) || currentHash === link.hash) ) {
            subMenuItems.forEach((li) => li.classList.remove("current"));
            item.classList.add("current");
        }
    });

    // Add # to the first submenu item
    document.addEventListener('DOMContentLoaded', function () {
        const submenuWrapper = document.querySelector('#toplevel_page_easycommerce-store .wp-submenu');
        if (submenuWrapper) {
            const firstItem = submenuWrapper.querySelector('li.wp-first-item a');
            if (firstItem && !firstItem.href.endsWith('#')) {
                firstItem.href += '#';
            }
        }
    });

    //checkout templates @todo remove when settings page is in react
    jQuery(document).ready(function($) {

        var templateImages = {
            'template-1': EASYCOMMERCE.assets + 'admin/img/checkout-templates/template-1.png',
            'template-2': EASYCOMMERCE.assets + 'admin/img/checkout-templates/template-2.png',
            'template-3': EASYCOMMERCE.assets + 'admin/img/checkout-templates/template-3.png',
        };

        var $select = $('#easycommerce-field-checkout_template');
        var $wrapper = $('#easycommerce-field-wrapper-checkout_template');

        if ($('#easycommerce-checkout-template-preview').length === 0) {
            $wrapper.after('<img id="easycommerce-checkout-template-preview" alt="Template Preview" src="" style="display:none;margin-top:10px;max-width:100%;border:1px solid #ddd;border-radius:8px;">');
        }

        var $img = $('#easycommerce-checkout-template-preview');
        var $button = $('#easycommerce-checkout-template-preview-button');

        function updateTemplatePreview() {
            var selectedValue = $select.val();
            var imgUrl = templateImages[selectedValue];
            if (imgUrl) {
                $img.attr('src', imgUrl);
            } else {
                $img.hide();
            }
        }

        updateTemplatePreview();
        $select.on('change', updateTemplatePreview);
        $button.on('click', function() {
            $img.toggle();
        });
    });
});