import _ from "lodash"
import axios from 'axios';
import { chunk } from 'lodash';
import { defineStore } from 'pinia'
import { useBoutiqueStore } from "./BoutiqueStore"
// import { fromUnixTime, format } from 'date-fns'

const getFilterable = (movie, state, array) => {
    let boolArray = []
    movie['filterable'].forEach(f => {
        for (let item of state.filter[array]) {
            boolArray.push(item.includes(f))
        }
    })
    return boolArray.filter(Boolean).length >= 1
}

export const useMovieStore = defineStore('movies', {
    state: () => ({
        apiKey: null,
        baseUrl: 'https://api.themoviedb.org/3/',
        movieAppend: '&append_to_response=videos,images,release_dates,credits',
        subUrls: {
            search: 'search/movie',
            searchPeople: 'search/person',
            movie: 'movie/',
        },
        movies: [],
        filter: [[], []],
        filter_formats: [],
        filter_boutiques: [],
        counts: {},
        activeSearchedMovie: null,
        activeSearchedMovieDialog: false,
        activeSearchedMovieDataFull: null,
        query: "",
        activeMovieDialog: false,
        activeMovie: null,
        creditMovieDialog: false,
        activeCredit: null,
        editMovieDialog: false,
        editableMovie: null,
        deleteConfirmationDialog: false,
        showWishList: false,
        rowSize: 6,
        currentPage: null,
        lastPage: null,
        movieCount: null,
        countDrawer: null,
        mdbids: [],
    }),

    getters: {
        moviesFiltered(state) {
            let movies = state.movies;

            if (state.filter[0].length) {
                movies = movies.filter(movie => getFilterable(movie, state, 0))
            }

            if (state.filter[1].length) {
                movies = movies.filter(movie => getFilterable(movie, state, 1))
            }

            if (state.query != "") {
                movies = movies
                    .sort((a, b) =>
                        a.sortable_name > b.sortable_name ? 1 : -1
                    )
                    .map(m => { return { ...m, searchable_name: m.name.toLowerCase() } })
                    .filter(m => { return m.searchable_name.includes(state.query) })
            }

            return { movies: chunk(movies, state.rowSize), length: movies.length, movies_unchunk: movies }
        },
        formats(state) {
            if (!state.movies || !state.movies.length) return []

            let movies = state.filter[0].length
                ? state.movies.filter(movie => getFilterable(movie, state, 0))
                : state.movies;

            return movies.reduce((acc, value) => {
                let result = {
                    '4k_disc': ['4K', 1, 0],
                    '4k_digital': ['4K', 0, 1],
                    'hd_disc': ['HD', 1, 0],
                    'hd_digital': ['HD', 0, 1],
                    'sd_disc': ['SD', 1, 0],
                    'sd_digital': ['SD', 0, 1],
                }[value.filter_on_quality]

                acc[result[0]][0] = acc[result[0]][0] + result[1]
                acc[result[0]][1] = acc[result[0]][1] + result[2]
                return acc
            }, { '4K': [0, 0], 'HD': [0, 0], 'SD': [0, 0] })
        },
    },

    actions: {
        composeURL(subUrl, additionals = null, useEnd = true) {

            let additionalsString = Object.entries(additionals).reduce((acc, [key, value]) => {
                let string = '&' + key + '=' + value;
                return acc + string;
            }, '')

            const start = this.baseUrl + subUrl + '?api_key=' + this.apiKey;
            const end = '&language=en-US&page=1&include_adult=true'
            // const end = '&language=en-US&page=1&include_adult=false'
            // const end = '&append_to_response=videos,images,release_dates,credits'
            return useEnd ? `${start}${additionalsString}${end}` : `${start}${additionalsString}`
        },
        async getMovies() {
            try {
                const results = await axios.get(route('movies.get', {
                    _query: {
                        page: this.currentPage ? this.currentPage : null,
                        wish_list: this.showWishList,
                        term: this.query,
                        filter: this.filter.flat().length ? JSON.stringify(this.filter) : '',
                        formats: this.filter_formats.length ? JSON.stringify(this.filter_formats) : '',
                        boutiques: this.filter_boutiques.length ? JSON.stringify(this.filter_boutiques) : '',
                    },
                }))

                this.getMovieDatabaseIDs();

                const { data, current_page, last_page, total } = results.data;
                this.currentPage = current_page
                this.lastPage = last_page

                if (this.movies.length < total) {
                    this.movies = [
                        this.movies,
                        data.map(m => { return { ...m, loading: false } })
                    ].flat();
                }

            } catch (error) {
                return error
            }
        },
        async getMoviesCount() {
            const counts = await axios.get(route('movies.count'));
            this.counts = counts.data;
        },
        async getMovieDatabaseIDs() {
            const ids = await axios.get(route('movies.mdb_ids'));
            this.mdbids = ids.data.map(mid => mid.movie_db_id);
        },
        onScrollIntersection() {
            if (this.currentPage < this.lastPage) {
                this.currentPage += 1;
                this.getMovies();
            }
        },
        resetMovies() {
            this.currentPage = null
            this.lastPage = null
            this.movies = []
            this.getMoviesCount();
        },
        async searchLocalMovies(query) {
            this.query = query
            this.resetMovies()
            this.getMovies();
        },
        async searchMovies(query) {
            try {
                const results = await axios.get(this.composeURL(this.subUrls.search, { query }))
                return results.data
            } catch (error) {
                return error
            }
        },
        async searchPeople(query) {
            try {
                const results = await axios.get(this.composeURL(this.subUrls.searchPeople, { query }))
                return results.data
            } catch (error) {
                return error
            }
        },
        async searchMoviesForPerson(query) {
            try {
                let url = this.composeURL(`person/${query.id}/movie_credits`, { query });
                console.log('url', url)
                let results = await axios.get(url)
                const { cast, crew } = results.data
                return _.unionBy(cast, crew, 'id')
            } catch (error) {
                return error
            }
        },
        async storeMovie(payload) {
            try {
                console.log({ payload })
                let result = await axios.post(route('movies.store'), payload)
                this.resetMovies()
                this.getMovies()
                return result.data
            } catch (error) {
                return error
            }
        },
        async setActiveSearchMovie(movie) {
            let film;
            if (movie) {
                let url = `${this.baseUrl}/movie/${movie.id}?api_key=${this.apiKey}${this.movieAppend}`
                film = await axios.get(url)
            }

            this.activeSearchedMovie = movie
            this.activeSearchedMovieDataFull = movie ? film.data : null;

        },
        async setActiveMovie(movie) {
            try {
                if (this.activeMovie) {
                    this.activeMovie = null
                }

                let result = await axios.get(route('movies.show', movie))
                this.activeMovie = result.data;
                return true;
            } catch (error) {
                return error;
            }
        },
        async setActiveRating(rating) {
            await axios.patch(route('movies.update_single', this.activeMovie.id), { value: rating, type: 'rating' })
        },
        async setActiveWatched(watched) {
            await axios.patch(route('movies.update_single', this.activeMovie.id), { value: watched, type: 'watched' })
            this.movies = this.movies.map(movie => {
                if (movie.id == this.activeMovie.id) return { ...movie, watched }
                return movie
            })
        },
        async setSingleUpdate(value, type) {
            await axios.patch(route('movies.update_single', this.activeMovie.id), { value, type })
        },
        setActiveMovieDialog() {
            this.activeMovieDialog = true;
        },
        unsetActiveMovie() {
            this.activeMovieDialog = false;
            setTimeout(() => {
                this.activeMovie = null;
            }, 1000)
        },
        setActiveSearchMovieDialog(state) {
            this.activeSearchedMovieDialog = state
        },
        toggleFilteredItem(payload) {
            let { filter, include, array } = payload

            if (include) {
                this.filter[array] = [...this.filter[array], filter]
            } else {
                this.filter[array] = this.filter[array].filter(f => f != filter)
            }

            this.resetMovies()
            this.getMovies();
        },
        updateFilter(item) {
            const { filter, type, include } = item
            if (include) {
                this[`filter_${filter}s`] = [...this[`filter_${filter}s`], type]
            } else {
                this[`filter_${filter}s`] = this[`filter_${filter}s`].filter(f => f != type);
            }

            this.resetMovies()
            this.getMovies();
        },
        setCreditMovieDialog(state) {
            this.creditMovieDialog = state;
            return Promise.resolve(true)
        },
        async setActiveCredit(person) {
            let result = await axios.get(route('person', person.id));
            this.activeCredit = result.data;
            this.creditMovieDialog = true;
        },
        setEditMovieDialog(state) {
            this.editMovieDialog = state;
        },
        async setEditableMovie() {
            try {
                const results = await axios.get(
                    this.composeURL(
                        `${this.subUrls.movie}${this.activeMovie.movie_db_id}`,
                        { '': this.movieAppend },
                        false
                    )
                )

                this.editableMovie = {
                    ...results.data,
                    ...this.activeMovie,
                    poster_index: this.currentImageIndex(results.data.images.posters, this.activeMovie.active_poster_id),
                    backdrop_index: this.currentImageIndex(results.data.images.backdrops, this.activeMovie.active_backdrop_id),
                }
            } catch (error) {
                return error
            }
        },
        unsetEditableMovie() {
            this.editableMovie = null;
        },
        currentImageIndex(images, image_path) {
            return images.findIndex(image => image.file_path == image_path)
        },
        async updateMovie({ form, movie }) {
            try {
                let result = await axios.put(route('movies.update', movie), form)
                this.setActiveMovie(result.data.id);
            } catch (error) {
                return error;
            }
        },
        setDeleteConfirmationDialog(state) {
            this.deleteConfirmationDialog = state
        },
        async deleteSelectedMovie(movie) {
            try {
                let result = await axios.delete(route('movies.destroy', movie))
                return result.data
            } catch (error) {
                return error
            }
        },
        setWishList(state) {
            const boutique = useBoutiqueStore();
            this.showWishList = state;
            this.resetMovies()
            this.getMovies();
            boutique.getBoutiques(state)
        },
        async addToCatalog(movie) {
            try {
                await axios.post(route('movies.remove_wishlist', movie))
                this.resetMovies()
                this.getMovies();
            } catch (error) {
                console.log(error)
            }
        },
        setRowSize(size) {
            this.rowSize = size;
        },
        setApiKey(key) {
            this.apiKey = key;
        },
        async updateNote(movie, note, action) {
            try {
                return (await axios.post(route('movies.update_note', movie), { note, action })).data;
            } catch (error) {
                console.log(error);
            }
        },
        setCountDrawer(state) {
            this.countDrawer = state;
        }
    },

    // debounce: {
    //     searchMovies: 300,
    // },

    // throttle: {
    //     searchMovies: 500,
    // }
});