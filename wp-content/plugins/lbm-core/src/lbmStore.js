import { create } from "zustand";

const lbmStore = create((set) => {
    const today = new Date().toISOString().split("T")[0];

    return {
        // ✅ Existing states
        availabilityData: [],
        loading: false,
        selectedDate: today, // Initialize with today's date
        productId: null,
        selectedAddOnId: null,
        selectedAddOns: [],
        productPrice: 0,
        selectedServiceType: '',
        addonsChanged: false,

        // ✅ New state for Mobile Travel Fee
        mobileTravelFee: 0,
        readyForMobile: false,
        addonTotalTime: 0,
        errorMessage: "Postcode cannot be empty",

        // ✅ Existing actions
        setAvailabilityData: (data) => set({ availabilityData: data, loading: false }),
        setLoading: (loading) => set({ loading }),
        setSelectedDate: (date) => set({ selectedDate: date }),
        setProductId: (id) => set({ productId: id }),
        setSelectedAddOnId: (addonId) => set({ selectedAddOnId: addonId }),
        setSelectedAddOns: (addOns) => set({ selectedAddOns: addOns }),
        setProductPrice: (price) => set({ productPrice: price }),
        setSelectedServiceType: (serviceType) => set({ selectedServiceType: serviceType }),

        // ✅ New action to set Mobile Travel Fee
        setMobileTravelFee: (fee) => set({ mobileTravelFee: fee }),
        setReadyForMobile: (isReady) => set({ readyForMobile: isReady }),
        setAddonsChanged: () => set({ addonsChanged: true }),

        // ✅ Reset addonsChanged after fetch
        resetAddonsChanged: () => set({ addonsChanged: false }),
        setErrorMessage: (message) => set({ errorMessage: message }),
    };
});

export default lbmStore;
