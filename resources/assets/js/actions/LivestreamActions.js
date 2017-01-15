import axios from 'axios';
import { browserHistory } from 'react-router';
import { updateGlobalRequests } from './AuthActions';



export const REQUEST_LIVESTREAM = 'REQUEST_LIVESTREAM';
export const RECEIVE_LIVESTREAMS = 'RECEIVE_LIVESTREAMS';
export const RECEIVE_LIVESTREAM = 'RECEIVE_LIVESTREAM';
export const RECEIVE_LIVESTREAM_FAILURE = 'RECEIVE_LIVESTREAM_FAILURE';
export const LIVESTREAM_SUBSCRIBED = 'LIVESTREAM_SUBSCRIBED';




function requestLivestream() {

	return {
		type: REQUEST_LIVESTREAM
	}
}


function livestreamSubscribed() {

	return {
		type: LIVESTREAM_SUBSCRIBED
	}
}



function receiveLivestreams(livestreams) {

	return {
		type: RECEIVE_LIVESTREAMS,
		data: livestreams
	}
}





function receiveLivestream(livestream) {

	return {
		type: RECEIVE_LIVESTREAM,
		data: livestream
	}
}




function requestLivestreamFailure(err) {

	return {
		type: RECEIVE_LIVESTREAM_FAILURE,
		error: err
	}
}






function shouldSubscribeToLivestream(livestream) {
		 
	if (livestream.isSubscribed) {
	
	    return false;
	} 
	else {
		return true;
	}
}






export function subscribeToLivestreamIfNeeded() {
	
	
	return function(dispatch, getState, Echo) {
		
		let { livestream } = getState();

		if (shouldSubscribeToLivestream(livestream)) {
		    
		    try {
			    Echo.channel('opruut.livestream')
				    .listen('OpruutRequestLivestreamArrived', (event) => {
				        // console.log('OpruutRequestLivestreamArrived : ', event);

				        let globalRequests = event.globalRequests;
				        
				        // update Globar requests
				        dispatch(updateGlobalRequests(globalRequests));

				        // now add data to livestream requests
				        dispatch(receiveLivestream(event))

				    });


				Echo.channel('opruut.livestream.favorites')
				    .listen('FavoriteActionsLivestreamArrived', (event) => {
				        // console.log('FavoriteActionsLivestreamArrived : ', event);

				        // now add data to livestream requests
				        dispatch(receiveLivestream(event))

				    });
				
				// if no error in subscribing to channel then dispatch channel subsribed action
				return dispatch(livestreamSubscribed());
			
			}
			catch (err) {
				// console.log('ERROR : ', error);
				return dispatch(requestLivestreamFailure(error));
			}
		}
	};
}














function fetchLivestream() {

	return function(dispatch) {
		
		dispatch(requestLivestream());

		axios.post('/api/v1/fetch/livestream')
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    let livestreams = response.data.livestreams; // an array of objects
		    		    
		    return dispatch(receiveLivestreams(livestreams));
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
			return dispatch(requestLivestreamFailure(error));
		});

	}
}






function shouldFetchLivestream(livestream) {
		 
	if (livestream.data.length === 0) {
		return true
	}
	else if (livestream.isFetching) {
	
	    return false;
	} 
	else if (livestream.data.length > 0) {
		return false;
	}
	else {
		return true;
	}
}







export function fetchLivestreamIfNeeded() {
	
	
	return function(dispatch, getState, Echo) {
		
		let { livestream } = getState();

		if (shouldFetchLivestream(livestream)) {

			return dispatch(fetchLivestream());
		}
	};
}






