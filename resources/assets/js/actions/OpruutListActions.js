import axios from 'axios';
import { browserHistory } from 'react-router';
import { toggleValidateStatusIfPresent } from './FavoritesActions';


export const REQUEST_OPRUUTS = 'REQUEST_OPRUUTS';
export const RECEIVE_OPRUUTS = 'RECEIVE_OPRUUTS_FAILURE';
export const RECEIVE_OPRUUTS_FAILURE = 'REQUEST_OPRUUTS_FAILURE';
export const ADD_OPRUUT_TO_LIST = 'ADD_OPRUUT_TO_LIST';
export const UPDATE_CURSOR_OPRUUT_LIST = 'UPDATE_CURSOR_OPRUUT_LIST';
export const UPDATE_LIMIT_OPRUUT_LIST = 'UPDATE_LIMIT_OPRUUT_LIST';
export const TOGGLE_FAVORITE_STATUS_FROM_HOME = 'TOGGLE_FAVORITE_STATUS_FROM_HOME';



const BaseRequestData = {

	userid: null,
	favorites: false,
	general: true

};





function requestOpruuts() {
	// console.log('Action: OpruutListActions : requestOpruuts, arguement : () : no arguments');

	return {
		type: REQUEST_OPRUUTS
	}
}



function updateCursor(cursor) {
	// console.log('Action: OpruutListActions : updateCursor, arguement : (cursor) : ', cursor);

	return {
		type: UPDATE_CURSOR_OPRUUT_LIST,
		cursor
	}
}



function updateLimit(limit) {
	// console.log('Action: OpruutListActions : updateLimit, arguement : (limit) : ', limit);

	return {
		type: UPDATE_LIMIT_OPRUUT_LIST,
		limit
	}
}


function receiveOpruuts(opruuts) {
	// console.log('Action: OpruutListActions : receiveOpruuts, arguement : (opruuts) : ', opruuts);

	return {
		type: RECEIVE_OPRUUTS,
		data: opruuts
	}
}



function receiveOpruutsFailure(err) {
	// console.log('Action: OpruutListActions : receiveOpruutsFailure, arguement : (err) : ', err);

	return {
		type: RECEIVE_OPRUUTS_FAILURE,
		error: err
	}
}




export function addOpruutResultToOpruutList(opruut) {
	// console.log('Action: OpruutListActions : receiveOpruuts, arguement : (opruut) : ', opruut);

	// now before adding opruut to result list, we need to increment the cursor
	// count since newxt when more opruuts will be fetched, no duplicates are fetched
	// as the count of orpuuts increased at the server
	// 

	return {
		type: ADD_OPRUUT_TO_LIST,
		data: opruut
	}
}





function fetchOpruutList(cursor, limit, fetch_type) {
	// console.log('Action: OpruutListActions : type: dispatch: outside: fetchOpruutList, arguement : (cursor, limit, fetch_type) : ', cursor, limit, fetch_type);
	
	return function(dispatch) {

		// console.log('Action: OpruutListActions : type: dispatch: outside: fetchOpruutList, arguement : (cursor, limit, fetch_type) : ', cursor, limit, fetch_type);
		
		
		let fetch_url = '/api/v1/fetch/opruuts';
		
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


		

		dispatch(requestOpruuts())
		
		axios.post(fetch_url)
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    let opruuts = response.data.opruuts; // an array of objects
		    let cursor = response.data.cursor; 
		    let limit = response.data.limit;  

		    dispatch(updateCursor(cursor));
			dispatch(updateLimit(limit));

			return dispatch(receiveOpruuts(opruuts));
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
			return dispatch(receiveOpruutsFailure(error));
		});
	
	};

}






function shouldFetchOpruutList(opruuts) {
	// console.log('Action: OpruutRequestActions : shouldFetchOpruut, arguement : (opruuts) : ', opruuts);	
	
	if (opruuts.isFetching) {
		// only allow one parallele request
		// console.log('opruuts.isFetching is true, hence returning false');
	    return false;
	}  
	else {
		// fetch list after list being invalidated
		// console.log('fetch list after since applicable hence returning true ');
		return true;
	}
}






export function fetchOpruutListIfNeeded(requestData, fetch_type) {
	
	if (requestData) {
		requestData = {...BaseRequestData, ...requestData };
	}
	
	// console.log('Action: OpruutListActions : type: dispatch: outside: fetchOpruutListIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
	
	return function(dispatch, getState) {

		// console.log('Action: OpruutListActions : type: dispatch: inside: fetchOpruutListIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
		
		let { opruuts } = getState();

		if (shouldFetchOpruutList(opruuts)) {
			// console.log('Action: OpruutListActions : type: dispatch: inside: shouldFetchOpruutList is true, hencing fetching list');
		    
		    return dispatch(fetchOpruutList(opruuts.cursor, opruuts.limit, fetch_type))
		}
		else {
			// console.log('Action: OpruutListActions : type: dispatch: inside: shouldFetchOpruutList is false, hencing not fetching list');
		}
	};
}







function shouldFetchFirstTimeOpruutList(opruuts) {
	// console.log('Action: OpruutRequestActions : shouldFetchFirstTimeOpruutList, arguement : (opruuts) : ', opruuts);	
	
	if (opruuts.isFetching) {
		// only allow one parallele request
		// console.log('opruuts.isFetching is true, hence returning false');
	    return false;
	} 
	else if (opruuts.data.length === 0) {
		// pull if list is emepty
		// console.log('opruuts.data.length is 0, hence returning true');
	    return true;
	} 
	else {
		// fetch list after list being invalidated
		// console.log('opruuts.firstTimeHome is false, hence returning false');
		return false;
	}
}





export function fetchOpruutListFirstTimeIfNeeded(requestData, fetch_type) {
	
	if (requestData) {
		requestData = {...BaseRequestData, ...requestData };
	}
	
	// console.log('Action: OpruutListActions : type: dispatch: outside: fetchOpruutListFirstTimeIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
	
	return function(dispatch, getState) {

		// console.log('Action: OpruutListActions : type: dispatch: inside: fetchOpruutListFirstTimeIfNeeded, arguement : (requestData, fetch_type) : ', requestData, fetch_type);
		
		let { opruuts } = getState();

		if (shouldFetchFirstTimeOpruutList(opruuts)) {
			// console.log('Action: OpruutListActions : type: dispatch: inside: shouldFetchOpruutList is true, hencing fetching list');
		    
		    return dispatch(fetchOpruutList(opruuts.cursor, opruuts.limit, fetch_type))
		}
		else {
			// console.log('Action: OpruutListActions : type: dispatch: inside: shouldFetchOpruutList is false, hencing not fetching list');
		}
	};
}









export function toggleFavoriteStatusFromHome(opruutId, isFavorited) {
	// console.log('Action: OpruutListActions : toggleFavoriteStatusFromHome, arguement : (opruutId, isFavorited) : ', opruutId, isFavorited);

	return {
		type: TOGGLE_FAVORITE_STATUS_FROM_HOME,
		opruutId: parseInt(opruutId),
		isFavorited
	}

}





export function toggleFavoriteFromHome(opruutId, isFavorited) { 
	// console.log('Action: OpruutListActions : type: dispatch: outside: toggleFavoriteFromHome, arguement : (opruutId, isFavorited) : ', opruutId, isFavorited);
	
	return function(dispatch, getState) {

		// console.log('Action: OpruutListActions : type: dispatch: inside: toggleFavoriteFromHome, arguement : (opruutId, isFavorited) : ', opruutId, isFavorited);
		
		let fetch_url = `/api/v1/opruut/favorite/toggle?opruutId=${opruutId}`;

		// first toggle the favorite status to immediately show a positive feedback to user
		dispatch(toggleFavoriteStatusFromHome(opruutId));

		axios.post(fetch_url)
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    let status = response.data.status; // an array of objects

		    if (status !== 'success') {
		    	// if not able to toggle at server, toggle status at client again	 
			    return dispatch(toggleFavoriteStatusFromHome(opruutId));
		    }
		    else {
		    	// since toggling is successfull
		    	// check if the request was for favoriting or unfavoriting
		    	// if it was for favoriting, then try to validate
		    	// the favorited opruut in favorite store if present
		    	// or, else if it was for unfavoriting
		    	// then invalidate the unfavorited opruut in the favorites store
		    	// if present
		    	return dispatch(toggleValidateStatusIfPresent(opruutId, isFavorited));
		    }
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
		    // toggle status again if error in toggling at server
			return dispatch(toggleFavoriteStatusFromHome(opruutId));
		});
	
	};

}




