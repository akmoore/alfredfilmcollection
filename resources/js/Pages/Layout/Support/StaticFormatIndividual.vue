<template>
  <v-list-item :title="title">
    <v-list-item-subtitle>
      <span :class="medium == 'Disc' ? 'text-purple-accent-1' : 'text-blue-accent-1'" v-text="medium"></span>
      <v-chip size="x-small" variant="outlined" label class="ml-1"> {{count}} </v-chip>
    </v-list-item-subtitle>

    <template v-slot:append>
      <v-switch
        flat
        inset
        density="compact"
        :color="medium == 'Disc' ? 'purple-accent-1' : 'blue-accent-1'"
        hide-details
        v-model="on"
      ></v-switch>
    </template>
  </v-list-item>
  <v-divider inset></v-divider>
</template>

<script>
import {useMovieStore} from '@/Store/MovieStore.js';
export default {
  name: "StaticFormatIndividual",

  props: {
    title: String,
    medium: String,
    type: String,
    filter: String,
  },

  data() {
    return {
      on: false,
    };
  },

  computed:{
    count(){
        if(this.store.movies.length){
            return this.store.movies.reduce((acc, movie) => { 
                if(movie.filterable.items.includes(this.type)){
                    acc += 1
                }

                return acc;
            }, 0)
        }

        return 0;
    }
  },

  watch: {
    on(newVal) {
      this.toggleFilter(newVal)
    },
  },

  methods:{
    toggleFilter(newVal){
        this.$emit("toggle", { include: newVal, type: this.type, filter: this.filter });
    }
  },

  emits: ["toggle"],

  setup() {
    const store = useMovieStore();
    return {
        store,
    }
  },
};
</script>