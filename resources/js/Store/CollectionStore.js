import axios from 'axios';
import { defineStore } from 'pinia';

export const useCollectionStore = defineStore("collections", {
    state: () => ({
        collections: [],
        collectionDialog: false,
        boutiqueId: null,
    }),

    getters:{
        filteredCollections(state){
            if(!state.boutiqueId) return state.collections;

            return state.collections.filter(c => c.boutique_id == state.boutiqueId);
        }
    },

    actions: {
        async getCollections() {
            let result = await axios.get(route('collections.index'))
            this.collections = result.data
        },
        setCollectionDialog(state){
            this.collectionDialog = state;
        },
        setFilteredCollections(boutiqueId){
            this.boutiqueId = boutiqueId
        }
    }
})