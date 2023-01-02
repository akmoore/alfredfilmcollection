import axios from 'axios';
import { defineStore } from 'pinia';
import { useMovieStore } from './MovieStore';

export const useAuthStore = defineStore("auths", {
    state: () => ({
        user: null
    }),

    actions: {
        setUser(data) {
            this.user = data;
        },
        unsetUser() {
            this.user = null;
        },
        async logout(){
            const movieStore = useMovieStore();
            await axios.post(route('logout'))
            this.unsetUser();
            movieStore.getMovies();
        }
    }
})