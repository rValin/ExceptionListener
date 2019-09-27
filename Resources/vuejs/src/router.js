import Vue from 'vue';
import VueRouter from 'vue-router';
import Liste from './components/Liste';
import Exception from './components/Exception';

Vue.use(VueRouter);

export default new VueRouter({
    // mode: 'history',
    routes: [
        {path: '/', component: Liste, name: 'liste'},
        {path: '/exception/:id', component: Exception, name: 'exception'},
    ]
});
