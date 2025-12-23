<script setup>
import { ref, watch, onMounted } from 'vue';
import { useLocations } from '@/composables/useLocations';

const props = defineProps({
    country: {
        type: String,
        default: '',
    },
    city: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:country', 'update:city']);

const { countryNames, cities, selectedCountry, loadLocations } = useLocations();

// Local refs for v-model binding
const localCountry = ref(props.country);
const localCity = ref(props.city);

// Load locations on mount
onMounted(async () => {
    await loadLocations();
    // Set selectedCountry after locations are loaded
    if (props.country) {
        selectedCountry.value = props.country;
    }
});

// Watch for prop changes (for editing existing leads)
watch(() => props.country, (newValue) => {
    localCountry.value = newValue;
    selectedCountry.value = newValue;
});

watch(() => props.city, (newValue) => {
    localCity.value = newValue;
});

// Watch for local country changes
watch(localCountry, (newCountry) => {
    selectedCountry.value = newCountry;
    emit('update:country', newCountry);

    // Reset city if it's not in the new country's city list
    if (localCity.value && newCountry) {
        const countryData = cities.value;
        if (countryData && !countryData.includes(localCity.value)) {
            localCity.value = '';
            emit('update:city', '');
        }
    } else if (!newCountry) {
        // Clear city when country is cleared
        localCity.value = '';
        emit('update:city', '');
    }
});

// Watch for local city changes
watch(localCity, (newCity) => {
    emit('update:city', newCity);
});
</script>

<template>
    <div class="space-y-4">
        <!-- Country Dropdown -->
        <div>
            <label for="country" class="mb-2 block font-body text-sm font-medium text-light-black">
                Country
            </label>
            <div class="relative">
                <select
                    id="country"
                    v-model="localCountry"
                    class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple"
                >
                    <option value="">Select country</option>
                    <option v-for="country in countryNames" :key="country" :value="country">
                        {{ country }}
                    </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="h-5 w-5 text-zurit-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- City Dropdown -->
        <div>
            <label for="city" class="mb-2 block font-body text-sm font-medium text-light-black">
                City
            </label>
            <div class="relative">
                <select
                    id="city"
                    v-model="localCity"
                    :disabled="!localCountry || cities.length === 0"
                    class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 font-body text-sm text-light-black focus:border-zurit-purple focus:outline-none focus:ring-1 focus:ring-zurit-purple disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500"
                >
                    <option value="">{{ localCountry ? 'Select city' : 'Select country first' }}</option>
                    <option v-for="city in cities" :key="city" :value="city">
                        {{ city }}
                    </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="h-5 w-5 text-zurit-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>
