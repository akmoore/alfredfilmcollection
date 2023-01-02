<template>
  <v-list-item :title="title">
    <v-list-item-subtitle>
      <v-chip size="x-small" variant="outlined" label class="ml-1"> {{count}} </v-chip>
    </v-list-item-subtitle>

    <template v-slot:append>
      <v-switch
        flat
        inset
        density="compact"
        color="purple-accent-1"
        hide-details
        v-model="on"
      ></v-switch>
    </template>

    <template v-slot:prepend>
        <v-avatar rounded="0">
            <v-img v-if="image" :src="`/boutiques/${image}`"></v-img>
            <v-icon v-else icon="mdi-filmstrip"></v-icon>
        </v-avatar>
    </template>

  </v-list-item>
  <v-divider inset></v-divider>
</template>

<script>
import {useMovieStore} from '@/Store/MovieStore.js';
export default {
  name: "StaticBoutiqueIndividual",

  props: {
    title: String,
    image: String,
    // id: Number,
    type: Number,
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
                if(movie.boutique_id == this.type){
                    acc += 1;
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