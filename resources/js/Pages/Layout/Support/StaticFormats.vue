<template>
  <StaticFormatIndividual
    v-for="format in formats"
    :key="format.type"
    @toggle="updateFilter"
    :title="format.title"
    :medium="format.medium"
    :type="format.type"
    :filter="'format'"
  />
</template>

<script>
import { storeToRefs } from "pinia";
import { useMovieStore } from "@/Store/MovieStore.js";
import StaticFormatIndividual from "./StaticFormatIndividual.vue";
export default {
  name: "StaticFormats",

  components: {
    StaticFormatIndividual,
  },

  data() {
    return {
      formats: [
        {title: '4K', medium: 'Disc', type: '4k_disc'},
        {title: '4K', medium: 'Digital', type: '4k_digital'},
        {title: 'HD', medium: 'Disc', type: 'hd_disc'},
        {title: 'HD', medium: 'Digital', type: 'hd_digital'},
        {title: 'SD', medium: 'Disc', type: 'sd_disc'},
        {title: 'SD', medium: 'Digital', type: 'sd_digital'},
      ],
    };
  },

  methods: {
    toggleFormats(item) {
      console.log({ item });
    },
  },

  setup() {
    const store = useMovieStore();
    const { filter, movies } = storeToRefs(store);
    const { toggleFilteredItem, updateFilter } = store;

    return {
      store,
      filter,
      toggleFilteredItem,
      updateFilter,
    };
  },
};
</script>