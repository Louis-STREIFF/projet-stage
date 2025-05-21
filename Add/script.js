document.addEventListener('DOMContentLoaded', function () {
    const selectElement   = document.getElementById('format');
    const tagsContainer   = document.getElementById('selected-formats');
    const inputsContainer = document.getElementById('formats-inputs');
    const selectedValues  = new Set();

    selectElement.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        const value  = option.value;
        const label  = option.dataset.label;

        if (!value || selectedValues.has(value)) {
            this.selectedIndex = 0;
            return;
        }

        const tag = document.createElement('span');
        tag.textContent = label;
        tag.className = 'selected-formats';
        tag.style.marginRight = '8px';
        tag.style.cursor = 'pointer';

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'selectedFormats[]';
        hiddenInput.value = value;

        tagsContainer.appendChild(tag);
        inputsContainer.appendChild(hiddenInput);
        selectedValues.add(value);

        tag.addEventListener('click', function () {
            tagsContainer.removeChild(tag);
            inputsContainer.removeChild(hiddenInput);
            selectedValues.delete(value);
        });

        this.selectedIndex = 0;
    });
});
