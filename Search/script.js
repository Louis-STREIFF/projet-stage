document.addEventListener('DOMContentLoaded', function () {
    // const toleranceSlider = document.getElementById('tolerance');
    // const toleranceValue = document.getElementById('toleranceValue');
    const formatSelect = document.getElementById('format');
    const selectedFormatsContainer = document.getElementById('selected-formats');
    const selectedFormatsInput = document.getElementById('selectedFormatsInput');
    const bioInput = document.getElementById('bio');
    const triSelect = document.getElementById('tri');
    const lieuInput = document.getElementById('lieu');

    // toleranceValue.textContent = toleranceSlider.value;

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const debouncedOnInputChange = debounce(onInputChange, 500);

    // toleranceSlider.addEventListener('input', () => {
    //     toleranceValue.textContent = toleranceSlider.value;
    //     debouncedOnInputChange();
    // });

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

    function handleFormSubmit(event) {
        event.preventDefault();
        updateSelectedFormatsInput();

        const queryParams = new URLSearchParams({
            lieu: lieuInput.value,
            bio: bioInput ? bioInput.value : '',
            tri: triSelect ? triSelect.value : '',
            selectedFormats: selectedFormatsInput.value,
            // tolerance: toleranceSlider.value,
            lat: document.getElementById('lat').value,
            lng: document.getElementById('lng').value
        });

        const url = `${monPluginData.siteUrl}/resultat-recherche/?${queryParams.toString()}`;
        console.log("Redirection vers :", url);
        window.location.href = url;
    }

    function isFormatSelected(format) {
        return Array.from(selectedFormatsContainer.children).some(el => el.textContent === format);
    }

    function addSelectedFormat(format) {
        const el = createFormatElement(format);
        selectedFormatsContainer.appendChild(el);
        updateSelectedFormatsInput();
    }

    function createFormatElement(format) {
        const el = document.createElement('div');
        el.className = 'selected-format';
        el.textContent = format;
        el.style.cssText = 'display: inline-block; margin: 4px; padding: 6px 10px; background-color: #eee; border-radius: 20px; cursor: pointer;';
        el.addEventListener('click', () => {
            selectedFormatsContainer.removeChild(el);
            updateSelectedFormatsInput();
            debouncedOnInputChange();
        });
        return el;
    }

    function updateSelectedFormatsInput() {
        const formats = Array.from(selectedFormatsContainer.children).map(el => el.textContent);
        selectedFormatsInput.value = formats.join(',');
    }

    function onInputChange() {
        performSearch();
    }

    function performSearch(lat = null, lng = null) {
        const lieu = lieuInput.value;

        if (lieu && (!lat || !lng)) {
            geocodeLieu(lieu);
            return;
        }

        fetchResults(lat, lng);
    }

    function geocodeLieu(lieu) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: lieu }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const location = results[0].geometry.location;
                const lat = location.lat();
                const lng = location.lng();
                document.getElementById('lat').value = lat;
                document.getElementById('lng').value = lng;
                fetchResults(lat, lng);
            } else {
                console.warn('Geocoding failed:', status);
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
            // tolerance: toleranceSlider.value
        });

        if (lat !== null && lng !== null) {
            queryParams.append('lat', lat);
            queryParams.append('lng', lng);
        }

        fetch(monPluginData.ajaxUrl + '?action=search_artists&' + queryParams.toString())
            .then(response => response.text())
            .then(html => {
                document.getElementById('artists-list').innerHTML = html;
            })
            .catch(error => {
                console.error('Erreur lors de la recherche :', error);
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
                console.log("Lieu non trouvé.");
            }
        });
    };

    window.onload = initAutocomplete;

    const form = document.getElementById('searchForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
});
