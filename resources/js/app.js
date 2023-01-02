import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue'
import { createPinia } from 'pinia'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { vuetify } from '@/Plugins/Vuetify/vuetify'
import Layout from '@/Pages/Layout/Layout.vue'
import {debounce, throttle} from 'lodash'
import VueVirtualScroller from 'vue-virtual-scroller'
import 'vue-virtual-scroller/dist/vue-virtual-scroller.css'

createInertiaApp({
    resolve: (name) => {
        const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'))
        page.layout = Layout
        return page
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia()

        //REFERENCE: https://medium.com/@roperluo.me/anyone-else-who-doesn-39-t-know-pinia-please-accept-this-user-guide-including-plug-in-use-88badcce1c2e
        pinia.use(({options, store}) => {
            if(options.debounce){
                return Object.keys(options.debounce).reduce((acc, action) => {
                    acc[action] = debounce(store[action], options.debounce[action]);
                    return acc;
                }, {})
            }

            if(options.throttle){
                return Object.keys(options.throttle).reduce((acc, action) => {
                    acc[action] = throttle(store[action], options.throttle[action]);
                    return acc;
                }, {})
            }
        })

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .use(pinia)
            .use(VueVirtualScroller)
        app.config.globalProperties.$route = route
        app.mount(el)
    },
})