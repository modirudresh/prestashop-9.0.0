document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="gift_wrap_selection"]');
    const previewBox = document.getElementById('selected-wrapper-preview');
    const previewImg = document.getElementById('selected-wrapper-img');
    const submitBtn = document.getElementById('submitGiftWrapBtn');

    if (submitBtn) submitBtn.disabled = true;
    if (previewBox) previewBox.classList.add('d-none');

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                if (previewImg) previewImg.src = this.value;
                if (previewBox) previewBox.classList.remove('d-none');
                if (submitBtn) submitBtn.disabled = false;
            }
        });

        // Handle pre-selected on page load
        if (radio.checked) {
            if (previewImg) previewImg.src = radio.value;
            if (previewBox) previewBox.classList.remove('d-none');
            if (submitBtn) submitBtn.disabled = false;
        }
    });
});
