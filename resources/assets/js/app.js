// Import SCSS files
import 'react-toolbox/lib/commons.scss';


// Import CSS files
import '../css/bootstrap-dropmenu.css';
import '../css/bootstrap-notifications.css';
import '../css/auth.css';



import React from 'react';
import ReactDOM from 'react-dom';
import { Router, Route, IndexRoute, browserHistory } from 'react-router';
import { Provider } from 'react-redux';
import injectTapEventPlugin from 'react-tap-event-plugin';

// order of import here is import since laravel echo refers to socket-io's io class
// import io from 'socket.io-client';
// window.io = io;
import Echo from "laravel-echo";


import App from './components/App';
import HomeContainer from './containers/HomeContainer';
import SignupContainer from './containers/SignupContainer';
import LoginContainer from './containers/LoginContainer';
import FavoritesContainer from './containers/FavoritesContainer';
import RequireAuth from './containers/RequireAuth';

import configureStore from './store/configureStore';


// Needed for onTouchTap 
// http://stackoverflow.com/a/34015469/988941
injectTapEventPlugin();



// initialzise the Echo Instance
window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: 'http://localhost:6001',
});



// console.log('window.InitialState : ', window.InitialState);

// setup the store
const store = configureStore(window.InitialState, window.Echo);



let aboveTheFoldEl = document.getElementsByClassName('above-the-fold')[0];

let now = Date.now();
let above_the_fold_timer = window.ABOVE_THE_FOLD_TIMER;
// console.log(`now : ${now}, above_the_fold_timer : ${above_the_fold_timer}`);
let isRemovable = now - above_the_fold_timer;

if (isRemovable > 5000 ) {
	// remove the above th fold container
	aboveTheFoldEl.parentNode.removeChild(aboveTheFoldEl);
	renderReactApp();
}
else {
	setTimeout(() => { aboveTheFoldEl.parentNode.removeChild(aboveTheFoldEl); renderReactApp(); }, 5000 - isRemovable);
}




// // console.log('React App initiated');

function renderReactApp() {

	ReactDOM.render(
			
		<Provider store={store}>
			<Router history={browserHistory}>
				<Route path="/" component={App}>
					<IndexRoute component={HomeContainer} />
					<Route path="register" component={SignupContainer} />
					<Route path="login" component={LoginContainer} />
					{/* <Route path="favorites" component={RequireAuth(Favorites)} /> */}
					<Route path="favorites" component={RequireAuth(FavoritesContainer)} /> 
				</Route>
			</Router>
		</Provider>,

		document.getElementById('app')

	);

}






