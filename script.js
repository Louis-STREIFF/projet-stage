
document.addEventListener('DOMContentLoaded', function () {
    const toleranceSlider = document.getElementById('tolerance');
    const toleranceValue = document.getElementById('toleranceValue');
    const formatSelect = document.getElementById('format');
    const selectedFormatsContainer = document.getElementById('selected-formats');
    const selectedFormatsInput = document.getElementById('selectedFormatsInput');
    const bioInput = document.getElementById('bio');
    const triSelect = document.getElementById('tri');
    const lieuInput = document.getElementById('lieu');

    toleranceValue.textContent = toleranceSlider.value;

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const debouncedOnInputChange = debounce(onInputChange, 1000);

    toleranceSlider.addEventListener('input', () => {
        toleranceValue.textContent = toleranceSlider.value;
        debouncedOnInputChange();
    });

    if (formatSelect) {
        formatSelect.addEventListener('change', () => {
            const selectedFormat = formatSelect.value;
            if (selectedFormat && !isFormatSelected(selectedFormat)) {
                addSelectedFormat(selectedFormat);
                formatSelect.value = '';
                debouncedOnInputChange();
            } else if (selectedFormat) {
                alert('Already selected.');
                formatSelect.value = '';
            }
        });
    }

    if (bioInput) {
        bioInput.addEventListener('input', debouncedOnInputChange);
    }

    if (triSelect) {
        triSelect.addEventListener('change', debouncedOnInputChange);
    }

    function onInputChange() {
        performSearch();
    }

    function isFormatSelected(format) {
        return Array.from(selectedFormatsContainer.children)
            .some(el => el.textContent === format);
    }

    function addSelectedFormat(format) {
        const formatElement = createFormatElement(format);
        selectedFormatsContainer.appendChild(formatElement);
        updateSelectedFormatsInput();
    }

    function createFormatElement(format) {
        const formatElement = document.createElement('div');
        formatElement.className = 'selected-format';
        formatElement.textContent = format;
        formatElement.style.cssText = 'display: inline-block; margin: 4px; padding: 6px 10px; background-color: #eee; border-radius: 20px; cursor: pointer;';
        formatElement.addEventListener('click', function () {
            selectedFormatsContainer.removeChild(formatElement);
            updateSelectedFormatsInput();
        });
        return formatElement;
    }

    function updateSelectedFormatsInput() {
        const formats = Array.from(selectedFormatsContainer.children)
            .map(el => el.textContent);
        selectedFormatsInput.value = formats.join(',');
    }

    function performSearch(lat = null, lng = null) {
        const lieu = lieuInput.value;
        const bio = bioInput ? bioInput.value : '';
        const tri = triSelect ? triSelect.value : '';
        const formats = selectedFormatsInput.value;
        const tolerance = toleranceSlider.value;

        if (lieu && (!lat || !lng)) {
            geocodeLieu(lieu);
            return;
        }

        fetchResults(lat, lng);
    }

    function geocodeLieu(lieu) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({address: lieu}, function (results, status) {
            if (status === 'OK' && results[0]) {
                const location = results[0].geometry.location;
                const resolvedLat = location.lat();
                const resolvedLng = location.lng();
                document.getElementById('lat').value = resolvedLat;
                document.getElementById('lng').value = resolvedLng;
                fetchResults(resolvedLat, resolvedLng);
            } else {
                console.warn('Error:', status);
                fetchResults(null, null);
            }
        });
    }

    function fetchResults(lat, lng) {
        const queryParams = new URLSearchParams({
            lieu: lieuInput.value,
            bio: bioInput ? bioInput.value : '',
            tri: triSelect ? triSelect.value : '',
            selectedFormats: selectedFormatsInput.value,
            tolerance: toleranceSlider.value
        });

        if (lat !== null && lng !== null) {
            queryParams.append('lat', lat);
            queryParams.append('lng', lng);
        }

        fetch('search.php?' + queryParams.toString())
            .then(response => response.text())
            .then(html => {
                document.getElementById('all-artistes').innerHTML = html;
            })
            .catch(error => {
                console.error('Error', error);
            });
    }

    window.initAutocomplete = function () {
        const autocomplete = new google.maps.places.Autocomplete(document.getElementById('lieu'));
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (place.geometry) {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                document.getElementById('lat').value = lat;
                document.getElementById('lng').value = lng;
                performSearch(lat, lng);
            } else {
                console.log("No place found.");
            }
        });
    };

    window.onload = initAutocomplete;
});
