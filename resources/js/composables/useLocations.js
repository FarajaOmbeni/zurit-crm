import { ref, computed, shallowRef } from 'vue';

let locationData = null;
const isLoading = ref(false);
const isLoaded = ref(false);

export function useLocations() {
    const countries = shallowRef([]);
    const selectedCountry = ref('');

    const loadLocations = async () => {
        if (locationData) {
            countries.value = locationData.countries;
            isLoaded.value = true;
            return;
        }

        isLoading.value = true;
        try {
            const module = await import('@/data/locations.json');
            locationData = module.default;
            countries.value = locationData.countries;
            isLoaded.value = true;
        } finally {
            isLoading.value = false;
        }
    };

    const cities = computed(() => {
        if (!selectedCountry.value || !countries.value.length) return [];
        const country = countries.value.find(c => c.name === selectedCountry.value);
        return country?.cities || [];
    });

    const countryNames = computed(() => countries.value.map(c => c.name).sort());

    return {
        countries,
        countryNames,
        cities,
        selectedCountry,
        isLoading,
        isLoaded,
        loadLocations
    };
}
