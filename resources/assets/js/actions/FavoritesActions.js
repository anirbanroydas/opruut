import axios from 'axios';
import { browserHistory } from 'react-router';
import { toggleFavoriteStatusFromHome } from './OpruutListActions';


export const UPDATE_CURSOR_FAVORITES = 'UPDATE_CURSOR_FAVORITES';
export const UPDATE_LIMIT_FAVORITES = 'UPDATE_LIMIT_FAVORITES';
export const REQUEST_FAVORITES = 'REQUEST_FAVORITES';
export const RECEIVE_FAVORITES = 'RECEIVE_FAVORITES_FAILURE';
export const RECEIVE_FAVORITES_FAILURE = 'REQUEST_FAVORITES_FAILURE';
export const TOGGLE_FAVORITE_STATUS = 'TOGGLE_FAVORITE_STATUS';
export const ADD_OPRUUT_TO_FAVORITES = 'ADD_OPRUUT_TO_FAVORITES';
export const REMOVE_INVALID_DATA = 'REMOVE_INVALID_DATA';




const BaseRequestData = {

	userid: null,
	favorites: false,
	general: true

};





function updateCursor(cursor) {
	// console.log('Action: FavoritesActions : updateCursor, arguement : (cursor) : ', cursor);

	return {
		type: UPDATE_CURSOR_FAVORITES,
		cursor
	}
}



function updateLimit(limit) {
	// console.log('Action: FavoritesActions : updateLimit, arguement : (limit) : ', limit);

	return {
		type: UPDATE_LIMIT_FAVORITES,
		limit
	}
}






function requestFavoritess() {
	// console.log('Action: FavoritesListActions : requestFavoritess, arguement : () : no arguments');

	return {
		type: REQUEST_FAVORITES
	}
}


function receiveFavoritess(favorites) {
	// console.log('Action: FavoritesListActions : receiveFavoritess, arguement : (favorites) : ', favorites);

	return {
		type: RECEIVE_FAVORITES,
		data: favorites
	}
}



function receiveFavoritessFailure(err) {
	// console.log('Action: FavoritesListActions : receiveFavoritessFailure, arguement : (err) : ', err);

	return {
		type: RECEIVE_FAVORITES_FAILURE,
		error: err
	}
}








function fetchFavorites(cursor, limit, fetch_type) {
	// console.log('Action: FavoritesListActions : type: dispatch: outside: fetchFavoritesList, arguement : (cursor, limit, fetch_type) : ', cursor, limit, fetch_type);
	
	return function(dispatch) {

		// console.log('Action: FavoritesListActions : type: dispatch: inside: fetchFavoritesList, arguement : (cursor, limit, fetch_type) : ', cursor, limit, fetch_type);
		
		let fetch_url = '/api/v1/fetch/favorites';
		
		if (fetch_type === null || fetch_type === undefined) {
			fetch_url = `${fetch_url}?fetch_type=down`;
		}
		else {
			fetch_url = `${fetch_url}?fetch_type=${fetch_type}`;
		}
		
		if (limit !== null) {
			fetch_url = `${fetch_url}&limit=${limit}`;
		}
		if (cursor !== null) {
			fetch_url = `${fetch_url}&cursor=${cursor}`;
		}




		dispatch(requestFavoritess())
		
		axios.post(fetch_url)
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    let favorites = response.data.favorites; // an array of objects
		    let cursor = response.data.cursor; 
		    let limit = response.data.limit; 
	    	
		    dispatch(updateCursor(cursor));
			dispatch(updateLimit(limit));
			
			return dispatch(receiveFavoritess(favorites));
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
			return dispatch(receiveFavoritessFailure(error));
		});
	
	};

}





function shouldFetchFavorites(favorites) {
	// console.log('Action: OpruutRequestActions : shouldFetchOpruut, arguement : (favorites) : ', favorites);	
	
	if (favorites.isFetching) {
		// only allow one parallele request
		// console.log('favorites.isFetching is true, hence returning false');
	    return false;
	}
	else {
		// fetch list after list being invalidated
		// console.log('fetch list after since applicable hence returning true ');
		return true;
	}
}






export function fetchFavoritesIfNeeded(requestData, fetch_type) {
	
	if (requestData) {
		requestData = {...BaseRequestData, ...requestData };
	}
	
	// console.log('Action: FavoritesActions : type: dispatch: outside: fetchFavoritesIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
	
	return function(dispatch, getState) {

		// console.log('Action: FavoritesActions : type: dispatch: inside: fetchFavoritesIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
		
		let { favorites } = getState();

		if (shouldFetchFavorites(favorites)) {
			// console.log('Action: FavoritesActions : type: dispatch: inside: shouldFetchFavorites is true, hencing fetching list');
		    
		    return dispatch(fetchFavorites(favorites.cursor, favorites.limit, fetch_type))
		}
		else {
			// console.log('Action: FavoritesActions : type: dispatch: inside: shouldFetchFavorites is false, hencing not fetching list');
		}
	};
}







function shouldFetchFirstTimeFavorites(favorites) {
	// console.log('Action: OpruutRequestActions : shouldFetchFirstTimeFavorites, arguement : (favorites) : ', favorites);	
	
	if (favorites.isFetching) {
		// only allow one parallele request
		// console.log('favorites.isFetching is true, hence returning false');
	    return false;
	}
	else if (favorites.data.length === 0) {
		// pull if list is emepty
		// console.log('favorites.data.length is 0, hence returning true');
	    return true;
	} 
	else {
		// fetch list after list being invalidated
		// console.log('favorites.firstTimeHome is false, hence returning false');
		return false;
	}
}





export function fetchFavoritesFirstTimeIfNeeded(requestData, fetch_type) {
	
	if (requestData) {
		requestData = {...BaseRequestData, ...requestData };
	}
	
	// console.log('Action: FavoritesActions : type: dispatch: outside: fetchFavoritesFirstTimeIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
	
	return function(dispatch, getState) {

		// console.log('Action: FavoritesActions : type: dispatch: inside: fetchFavoritesFirstTimeIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
		
		let { favorites } = getState();

		if (shouldFetchFirstTimeFavorites(favorites)) {
			// console.log('Action: FavoritesActions : type: dispatch: inside: shouldFetchFirstTimeFavorites is true, hencing fetching list');
		    
		    return dispatch(fetchFavorites(favorites.cursor, favorites.limit, fetch_type))
		}
		else {
			// console.log('Action: FavoritesActions : type: dispatch: inside: shouldFetchFirstTimeFavorites is false, hencing not fetching list');
		}
	};
}






function shouldFetchNewFavorites(favorites) {
	// console.log('Action: OpruutRequestActions : shouldFetchNewFavorites, arguement : (favorites) : ', favorites);	
	
	if (favorites.data.length === 0) {
		// pull if list is emepty
		// hence returning false, means first time it has not been fetched
		// console.log('favorites.data.length is 0, hence returning false, means first time it has not been fetched');
	    return false;
	} 
	else {
		// fetch list after list being invalidated
		// console.log('favorites.data.length is not 0, hence returning true');
		return true;
	}
}





export function fetchFavoritesNewIfNeeded(requestData, fetch_type) {
	
	if (requestData) {
		requestData = {...BaseRequestData, ...requestData };
	}
	
	// console.log('Action: FavoritesActions : type: dispatch: outside: fetchFavoritesNewIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
	
	return function(dispatch, getState) {

		// console.log('Action: FavoritesActions : type: dispatch: inside: fetchFavoritesNewIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
		
		let { favorites } = getState();

		if (shouldFetchNewFavorites(favorites)) {
			// console.log('Action: FavoritesActions : type: dispatch: inside: shouldFetchNewFavorites is true, hencing fetching list');
		    
		    return dispatch(fetchFavorites(favorites.cursor, favorites.limit, 'up'))
		}
		else {
			// console.log('Action: FavoritesActions : type: dispatch: inside: shouldFetchNewFavorites is false, hencing not fetching list');
		}
	};
}







function addOpruutToFavorites(data) {
	// console.log('Action: FavoritesListActions : addOpruutToFavorites, arguement : (data) : ', data);

	return {
		type: ADD_OPRUUT_TO_FAVORITES,
		data
	}

}







function toggleFavoriteStatus(opruutId) {
	// console.log('Action: FavoritesListActions : toggleFavoriteStatus, arguement : (opruutId) : ', opruutId);

	return {
		type: TOGGLE_FAVORITE_STATUS,
		opruutId: parseInt(opruutId)
	}

}





export function toggleFavorite(opruutId, isFavorited) { 
	// console.log('Action: FavoritesListActions : type: dispatch: outside: toggleFavorite, arguement : (opruutId, isFavorited) : ', opruutId, isFavorited);
	
	return function(dispatch, getState) {

		// console.log('Action: FavoritesListActions : type: dispatch: inside: toggleFavorite, arguement : (opruutId, isFavorited) : ', opruutId, isFavorited);
		
		let fetch_url = `/api/v1/opruut/favorite/toggle?opruutId=${opruutId}`;

		// first toggle the favorite status to immediately show a positive feedback to user
		// generally toggling is for unfavoriting
		// since the moment the favorited status becomes false,
		// NOTE: we don't remove the opruut from the list
		// we just toggle the status which makes it the favorite heart status change
		// so that if we need to toggle back due to server error or 
		// for favorite back action again
		// we don't have to re-add it, instead
		// we will just toggle the status
		// NOTE: we will remove it from the list once the page is refreshed
		// at that time we will remove all the stale results, i.e unfavorited(isFavorited status is false)
		dispatch(toggleFavoriteStatus(opruutId));
		// also try to toggle the same status fro homecheck if opruut is present in the opruuts Store
    	// if present then apply the same toggle to the opruut
    	// and, if not present, then don't do anything
    	// NOTE: the entire thing is completely manages by the opruutListReducer
    	// which checks for the presence of the opruut with the given opruutId first and only then applies the effect
    	// So simply dispatch the toggleFavoriteStatusFromHome with the corresponding opruutId
		dispatch(toggleFavoriteStatusFromHome(opruutId));


		axios.post(fetch_url)
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    let status = response.data.status; // an array of objects

		    if (status !== 'success') {
		    	// if not able to toggle at server, toggle status at client again	
		    	// hence the opruut will be visible again in the list
		    	// accordign to the filter    	
			    
			    dispatch(toggleFavoriteStatus(opruutId));
			    return dispatch(toggleFavoriteStatusFromHome(opruutId));
		    }
		    else {
		    	// since toggling is successfull 
		    	// don't have to change anything
		    }
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
		    // toggle status again if error in toggling at server
			dispatch(toggleFavoriteStatus(opruutId));
			return dispatch(toggleFavoriteStatusFromHome(opruutId));
		});
	
	};

}








function shouldAddOpruutToFavorites(favorites) {
	// console.log('Action: OpruutRequestActions : shouldAddOpruutToFavorites, arguement : (favorites) : ', favorites);	
	
	if (favorites.cursor === null || favorites.cursor === undefined) {
		// pull if list is emepty
		// hence returning false, means first time it has not been fetched
		// console.log('favorites.cursor === null, hence returning false, means first time it has not been fetched');
	    return false;
	} 
	else {
		// console.log('favorites.cursor !== null, hence returning true');
		return true;
	}

}










export function toggleValidateStatusIfPresent(opruutId, isFavorited) {
	// console.log('Action: FavoritesListActions : type: dispatch: outside: toggleValidateStatusIfPresent, arguement : (opruutId, isFavorited) : ', opruutId, isFavorited);
	
	return function(dispatch, getState) {
		// console.log('Action: FavoritesListActions : type: dispatch: inside: toggleValidateStatusIfPresent, arguement : (opruutId) : ', opruutId, isFavorited);
		
		let { favorites } = getState();
		let isPresent = false;
		// firs tcheck if particular opruut exists in favorites store
		for (let opruut of favorites.data) {

			if (opruut.id === opruutId) {
				isPresent = true;
				// since the opruut is present in the favorites store
				// then check if the action is for favoriting or unfavoriting
				// if action is for favoriting, then validate(change the isFavorited status)
				// for the opruut if its false, if true( which theoreticall it shouldn't be), then leave as it is
				// or, if the action was to unfavorite, then make the same isFavorited status as false, 
				// if not false(which theoreitically it shouldn't be), leave as it is
				return dispatch(toggleFavoriteStatus(opruutId));
				break;
			}

		}			

		if (!isPresent) {
			// since opruut is not present in the favorites store
			// then check if the action was to favorite or unfavorite
			// if the action was to favorite
			// then add a new opruut to the favorites store
			// or, if the action was to unfaavorite and the 
			// opruut doesn't exist then don't do anything
			// as its not been loaded yet from the server, thus no need 
			// to invalidate or add it
			if (isFavorited === false) {
				// means the action was to favorite
				// add it to the start of favorites store
				// NOTE: only add if favorites has been fetched atleast
				// once, so that the same opruut is not fetched again
				// if, favorites has not been fetched yet, then don't 
				// add it. So that when the favorites are fetched for
				// the first time, it fetches all the lates favorited data
				// post that you can add it to the favorites list if required
				// as the new data will not be fetched until given a hard refresh
				if (shouldAddOpruutToFavorites(favorites)) {
					
					let { opruuts } = getState();
					for (let opruut of opruuts.data) {
						if (opruut.id === opruutId) {
							return dispatch(addOpruutToFavorites(opruut));
							break;
						}
					}
				}
				else {
					// since favorites are not fetched yet, just 
					// return as it is
				}
			}			

		}

	};

}









function removeOpruutsFromFavorites(invalids) {
	// console.log('Action: FavoritesListActions : removeOpruutsFromFavorites, arguement : (invalids) : ', invalids);

	return {
		type: REMOVE_INVALID_DATA,
		invalids
	}
}






export function refreshInvalidDataIfNeeded() {
	// console.log('Action: FavoritesListActions : type: dispatch: outside: refreshInvalidData, arguement : () : ');
	
	return function(dispatch, getState) {
		// console.log('Action: FavoritesListActions : type: dispatch: inside: refreshInvalidData, arguement : () : ');
		
		let { favorites } = getState();
		let invalids = [];
		let index = 0;
		// first find all invalid data in favorites store (i.e those opruuts whose isFavorited is false)
		for (let opruut of favorites.data) {
			if (opruut.isFavorited === false) {
				// means an invalid data
				invalids.push(index);
			}
			++index;
		}

		if (invalids.length > 0) {
			return dispatch(removeOpruutsFromFavorites(invalids));
		}
		else {
			// since nothing to invalid
			// hence don't do anythign
		}
	}
	
}

