const easycommerce_modal = (show = true) => {
    const modal = document.getElementById("easycommerce-modal");
    if (show) {
        modal.style.display = "";
    } else {
        modal.style.display = "none";
    }
};

const easycommerce_toast = (message = "", show = true) => {
    let toast = document.getElementById("easycommerce-toast");
    let content;

    if (!toast) {

        toast = document.createElement("div");
        toast.id = "easycommerce-toast";
        toast.style.cssText = `
            position: fixed;
            top: 120px;
            right: 20px;
            background-color: rgb(19 193 19);
            color: rgb(255, 255, 255);
            padding: 14px 20px;
            border-radius: 6px;
            border-bottom: 4px solid rgb(116 203 116);
            box-shadow: rgba(0, 0, 0, 0.15) 0px 4px 12px;
            font-family: sans-serif;
            font-size: 14px;
            z-index: 9999;
            min-width: 200px;
        `;

        content = document.createElement("div");
        content.className = "easycommerce-toast-message";
        toast.appendChild(content);

        document.body.appendChild(toast);
    } else {
        content = toast.querySelector(".easycommerce-toast-message");
    }

    if (content) content.textContent = message;
    toast.style.display = show ? "" : "none";

    // Auto-hide after 3s
    if (show) {
        setTimeout(() => {
            toast.style.display = "none";
        }, 1000);
    }
};

/**
 * Easycommerce new order shipping and billing address slide effect
 */
jQuery(function ($) {
    $(document).on("click", ".easycommerce-new-order-shipping", function () {
        $(".easycommerce-new-order-shipping-details").slideToggle();
    });
    $(document).on("click", ".easycommerce-new-order-billing", function () {
        $(".easycommerce-new-order-billing-details").slideToggle();
    });

    // Setting Screen Submenu scroll
    const submenu = $('#easycommerce-settings-submenus');
	if (submenu.length) {
		submenu.on('wheel', function(e) {
			const deltaY = e.originalEvent.deltaY;
			const deltaX = e.originalEvent.deltaX;
			if (deltaY !== 0 && deltaX === 0) {
				e.preventDefault();
				submenu.scrollLeft(submenu.scrollLeft() + deltaY);
			}
		});
	}
});
