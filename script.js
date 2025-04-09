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

    toleranceSlider.addEventListener('input', function() {
        toleranceValue.textContent = toleranceSlider.value;
        performSearch(); // Appeler la fonction pour mettre à jour les résultats
    });

    formatSelect.addEventListener('change', function () {
        const selectedFormat = formatSelect.value;
        if (selectedFormat && !isFormatSelected(selectedFormat)) {
            addSelectedFormat(selectedFormat);
            formatSelect.value = ''; // Réinitialise la sélection
            updateSelectedFormatsInput();
            performSearch();
        } else if (selectedFormat) {
            alert('Ce format a déjà été sélectionné.');
            formatSelect.value = ''; // Réinitialise la sélection
        }
    });

    if (bioInput) {
        bioInput.addEventListener('input', function () {
            performSearch();
        });
    }

    if (triSelect) {
        triSelect.addEventListener('change', function () {
            performSearch();
        });
    }

    function isFormatSelected(format) {
        const selectedFormats = Array.from(selectedFormatsContainer.children).map(el => el.textContent);
        return selectedFormats.includes(format);
    }

    function addSelectedFormat(format) {
        const formatElement = document.createElement('div');
        formatElement.className = 'selected-format';
        formatElement.textContent = format;
        formatElement.addEventListener('click', function () {
            selectedFormatsContainer.removeChild(formatElement);
            updateSelectedFormatsInput();
            performSearch();
        });
        selectedFormatsContainer.appendChild(formatElement);
    }

    function updateSelectedFormatsInput() {
        const formats = Array.from(selectedFormatsContainer.children).map(el => el.textContent);
        selectedFormatsInput.value = formats.join(',');
    }

    function performSearch(lat = null, lng = null) {
        const lieu = lieuInput.value;
        const bio = bioInput ? bioInput.value : '';
        const tri = triSelect ? triSelect.value : '';
        const formats = selectedFormatsInput.value;
        const tolerance = toleranceSlider.value;

        if (lieu && (!lat || !lng)) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: lieu }, function (results, status) {
                if (status === 'OK' && results[0]) {
                    const location = results[0].geometry.location;
                    const resolvedLat = location.lat();
                    const resolvedLng = location.lng();

                    document.getElementById('lat').value = resolvedLat;
                    document.getElementById('lng').value = resolvedLng;

                    performSearch(resolvedLat, resolvedLng);
                } else {
                    console.warn('Géocodification échouée :', status);
                    fetchResults(null, null);
                }
            });
            return;
        }

        fetchResults(lat, lng);
    }

    function fetchResults(lat, lng) {
        const queryParams = new URLSearchParams();
        const lieu = lieuInput.value;
        const bio = bioInput ? bioInput.value : '';
        const tri = triSelect ? triSelect.value : '';
        const formats = selectedFormatsInput.value;
        const tolerance = toleranceSlider.value;

        if (lieu) queryParams.append('lieu', lieu);
        if (bio) queryParams.append('bio', bio);
        if (tri) queryParams.append('tri', tri);
        if (formats) queryParams.append('selectedFormats', formats);
        if (lat !== null && lng !== null) {
            queryParams.append('lat', lat);
            queryParams.append('lng', lng);
        }
        queryParams.append('tolerance', tolerance);

        fetch('search.php?' + queryParams.toString())
            .then(response => response.text())
            .then(html => {
                const container = document.getElementById('all-artistes');
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Erreur lors de la recherche :', error);
            });
    }

    window.initAutocomplete = function () {
        const autocomplete = new google.maps.places.Autocomplete(lieuInput);
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry) {
                console.log("Aucune géométrie disponible pour le lieu saisi.");
                return;
            }
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            performSearch(lat, lng);
        });
    };

    window.onload = function () {
        initAutocomplete();
    };
});