document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="gift_wrap_selection"]');
    const previewBox = document.getElementById('selected-wrapper-preview');
    const previewImg = document.getElementById('selected-wrapper-img');
    const submitBtn = document.getElementById('submitGiftWrapBtn');

    if (submitBtn) submitBtn.disabled = true;
    if (previewBox) previewBox.style.display = 'none';

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                if (previewImg) previewImg.src = this.value;
                if (previewBox) previewBox.style.display = 'block';
                if (submitBtn) submitBtn.disabled = false;
            }
        });

        // Auto-enable if already selected
        if (radio.checked) {
            if (previewImg) previewImg.src = radio.value;
            if (previewBox) previewBox.style.display = 'block';
            if (submitBtn) submitBtn.disabled = false;
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const radios = document.querySelectorAll('input[name="gift_wrap_selection"]');
    const previewImg = document.getElementById('selected-wrapper-img');
    const previewBox = document.getElementById('selected-wrapper-preview');
    const submitBtn = document.getElementById('submitGiftWrapBtn');

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.checked) {
                previewImg.src = radio.value;
                previewBox.classList.remove('d-none');
                submitBtn.disabled = false;
            }
        });
    });
});
