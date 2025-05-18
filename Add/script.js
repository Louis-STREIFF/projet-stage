document.addEventListener('DOMContentLoaded', function () {
    const selectElement    = document.getElementById('format');
    const tagsContainer    = document.getElementById('selected-formats');
    const inputsContainer  = document.getElementById('formats-inputs');
    const selectedIds      = new Set();

    selectElement.addEventListener('change', function () {
        const opt   = this.options[this.selectedIndex];
        const id    = opt.value;
        const label = opt.dataset.label;

        if (!id || selectedIds.has(id)) {
            this.selectedIndex = 0;
            return;
        }
        const tag = document.createElement('div');
        tag.className = 'selected-format';
        tag.textContent = label;
        tag.style.cssText = `
            display: inline-block;
            margin: 4px;
            padding: 6px 10px;
            background-color: #eee;
            border-radius: 20px;
            cursor: pointer;
        `;
        tagsContainer.appendChild(tag);

        const hid = document.createElement('input');
        hid.type  = 'hidden';
        hid.name  = 'selectedFormats[]';
        hid.value = id;
        inputsContainer.appendChild(hid);

        selectedIds.add(id);

        tag.addEventListener('click', function () {
            tagsContainer.removeChild(tag);
            inputsContainer.removeChild(hid);
            selectedIds.delete(id);
        });

        this.selectedIndex = 0;
    });

    window.initAutocomplete = function () {
        const autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('lieu')
        );
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;
            document.getElementById('lat').value = place.geometry.location.lat();
            document.getElementById('lng').value = place.geometry.location.lng();
        });
    };
});
