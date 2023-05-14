import Dashboard from './Components/Dashboard.vue';
import EventHome from './Components/EventHome.vue';

export var routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            active: 'dashboard'
        }
    },
    {
        path: '/event/:id',
        name: 'event_home',
        component: EventHome,
        meta: {
            active: 'event_home'
        }
    }
];
