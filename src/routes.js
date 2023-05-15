import Dashboard from './Components/Dashboard.vue';
import EventHome from './Components/EventHome.vue';
import Administration from './Components/Adminstrator.vue';
import Attendees from './Components/Attendees.vue';
import IdCardPrinter from './Components/Parts/IdCardPrinter.vue';

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
    },
    {
        path: '/admin',
        name: 'admin',
        component: Administration,
        meta: {
            active: 'event_home'
        }
    },
    {
        path: '/attendees',
        name: 'attendees',
        component: Attendees,
        meta: {
            active: 'attendees'
        }
    },
    {
        path: '/id-printers',
        name: 'prints',
        component: IdCardPrinter,
        meta: {
            active: 'attendees'
        }
    }
];
