import axios from 'axios';
import { defineStore } from 'pinia'
// import {useAuthStore} from './AuthStore'

export const useBoutiqueStore = defineStore("boutiques", {
    state: () => ({
        boutiques: null,
        boutiqueDialog: false,
    }),

    actions: {
        async getBoutiques(wishListed = 0) {
            try {
                let results = await axios.get(route('boutiques.index', {
                    _query: {
                        wish_list: wishListed,
                    },
                }))
                this.boutiques = results.data.sort((a, b) => a.display > b.display ? 1 : -1)
            } catch (error) {
                return error
            }
        },
        async storeBoutique(payload) {
            let { boutique, image } = payload
            try {
                let formData = new FormData();

                formData.append("image", image[0]);
                formData.append("boutique", boutique);

                console.log({ formData });

                let results = await axios.post(route('boutiques.store'), formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })

                this.getBoutiques()
                return results.data
            } catch (error) {
                return error
            }

        },
        setBoutiqueDialog(state) {
            this.boutiqueDialog = state;
        }
    }
})

// var formData = new FormData();
// var imagefile = document.querySelector('#file');
// formData.append("image", imagefile.files[0]);
// axios.post('upload_file', formData, {
//     headers: {
//       'Content-Type': 'multipart/form-data'
//     }
// })