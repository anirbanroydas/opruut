import { createStore, compose, applyMiddleware } from 'redux';
// import ReduxPromise from 'redux-promise';
import thunkMiddleware from 'redux-thunk';
// import {persistStore, autoRehydrate} from 'redux-persist'
import rootReducer from '../reducers';
import * as Actions from '../actions';



export default function configureStore(initialState, Echo) {
	
	const store = createStore(
		rootReducer,
		initialState,
		compose (
			// applyMiddleware(ReduxPromise),
			// autoRehydrate(),
			applyMiddleware(thunkMiddleware.withExtraArgument(Echo)),
			window.devToolsExtension ? window.devToolsExtension() : f => f
		)
		
	);

	// persistStore(store);

	if (module.hot) {
		// Enable Webpack hot module replacement for reducers
		module.hot.accept('../reducers', () => {
			
			const nextRootReducer = require('../reducers').default;
			store.replaceReducer(nextRootReducer);

		});

	}

	// store.dispatch(Actions.verifyAuth());

	return store;

}