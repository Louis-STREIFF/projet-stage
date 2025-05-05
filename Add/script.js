document.addEventListener('DOMContentLoaded', function () {

    const selectElement = document.getElementById('format');
    const selectedFormatsContainer = document.getElementById('selected-formats');
    const hiddenInput = document.getElementById('selectedFormatsInput');
    const selectedFormats = [];

    selectElement.addEventListener('change', function () {
        const format = this.value;
        if (format && !selectedFormats.includes(format)) {
            selectedFormats.push(format);
            addFormatTag(format);
            updateSelectedFormatsInput();
        }
        this.selectedIndex = 0;
    });

    function addFormatTag(format) {
        const formatElement = document.createElement('div');
        formatElement.className = 'selected-format';
        formatElement.textContent = format;
        formatElement.style.cssText = 'display: inline-block; margin: 4px; padding: 6px 10px; background-color: #eee; border-radius: 20px; cursor: pointer;';
        formatElement.addEventListener('click', function () {
            selectedFormatsContainer.removeChild(formatElement);
            const index = selectedFormats.indexOf(format);
            if (index !== -1) selectedFormats.splice(index, 1);
            updateSelectedFormatsInput();
        });
        selectedFormatsContainer.appendChild(formatElement);
    }

    function updateSelectedFormatsInput() {
        hiddenInput.value = JSON.stringify(selectedFormats);
        console.log("Hidden input updated:", hiddenInput.value);
    }

    function initAutocomplete() {
        const autocomplete = new google.maps.places.Autocomplete(document.getElementById('lieu'));
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry) {
                console.log("No geometry found.");
                return;
            }
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
        });
    }

    initAutocomplete(); // appel direct ici, PAS dans window.onload
});
