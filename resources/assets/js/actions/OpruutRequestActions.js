import axios from 'axios';
import { browserHistory } from 'react-router';

import { addOpruutResultToOpruutList } from './OpruutListActions';



export const FIND_OPRUUT = 'FIND_OPRUUT';
export const GET_OPRUUT = 'GET_OPRUUT';
export const GET_OPRUUT_SUCCESS = 'GET_OPRUUT_SUCCESS';
export const GET_OPRUUT_FAILURE = 'FIND_OPRUUT_FAILURE';
export const INCOMPLETE_REQUEST_DATA = 'INCOMPLETE_REQUEST_DATA';
export const COMPLETE_REQUEST_DATA = 'COMPLETE_REQUEST_DATA';
export const CLEAR_OPRUUT = 'CLEAR_OPRUUT';
export const UPDATE_PREFERENCE = 'UPDATE_PREFERENCE';
export const UPDATE_RIDE_TIME = 'UPDATE_RIDE_TIME';
export const UPDATE_SOURCE_STATION_ID = 'UPDATE_SOURCE_STATION_ID';
export const UPDATE_DESTINATION_STATION_ID = 'UPDATE_DESTINATION_STATION_ID';
export const UPDATE_SOURCE_STATION_NAME = 'UPDATE_SOURCE_STATION_NAME';
export const UPDATE_DESTINATION_STATION_NAME = 'UPDATE_DESTINATION_STATION_NAME';
export const REMOVE_SOURCE_ID = 'REMOVE_SOURCE_ID';
export const REMOVE_DESTINATION_ID = 'REMOVE_DESTINATION_ID';



// PREFERENCE CONSTANTS
export const NO_PREFERENCE = 0;
export const TIME_PREFERENCE = 1;
export const COMFORT_PREFERENCE = 2;
export const BOTH_PREFERENCE = 3;

// RIDE TIME CONSTANTS
export const RIDE_NOW = 0;




export function updatePreference(preference) {
	// console.log('Action: OpruutRequestActions : updatePreference, arguement : (preference) : typeof(preference) ', preference, typeof preference);
	
	switch (preference) {
		case 0:
			preference = NO_PREFERENCE;
			break;
		case 1:
			preference = TIME_PREFERENCE;
			break;
		case 2:
			preference = COMFORT_PREFERENCE;
			break;
		case 3:
			preference = BOTH_PREFERENCE;
	}
	
	return {
		type: UPDATE_PREFERENCE,
		preference
	}
}


export function updateRideTime(rideTime) {
	// console.log('Action: OpruutRequestActions : updateRideTime, arguement : (rideTime) : typeof(ride_time) ', rideTime, typeof rideTime);
	
	return {
		type: UPDATE_RIDE_TIME,
		rideTime: rideTime
	}
}




export function updateSourceStation(source_id) {
	// console.log('Action: OpruutRequestActions : updateSourceStation, arguement : (source_id) : typeof(source_id)', source_id, typeof source_id);
	
	
	return function(dispatch, getState) {
		// console.log('Action: OpruutRequestActions : type: dispatch: inside:  updateSourceStation, arguement : no result  ');
		
		let { search } = getState();

		dispatch(updateSourceId(parseInt(source_id)));
		
		return dispatch(updateSourceName(search.station_list[source_id]));

	};



	
}


export function updateDestinationStation(destination_id) {
	// console.log('Action: OpruutRequestActions : updateDestinationStation, arguement : (destination_id) : ', destination_id);

	return function(dispatch, getState) {
		// console.log('Action: OpruutRequestActions : type: dispatch: inside:  updateDestinationStation, arguement : no result  ');
		
		let { search } = getState();

		dispatch(updateDestinationId(parseInt(destination_id)));
		
		return dispatch(updateDestinationName(search.station_list[destination_id]));

	};
}




function updateSourceId(source_id) {

	return {
		type: UPDATE_SOURCE_STATION_ID,
		source_id
	}

}



function updateSourceName(name) {

	return {
		type: UPDATE_SOURCE_STATION_NAME,
		name
	}

}





function updateDestinationId(destination_id) {

	return {
		type: UPDATE_DESTINATION_STATION_ID,
		destination_id
	}

}



function updateDestinationName(name) {

	return {
		type: UPDATE_DESTINATION_STATION_NAME,
		name
	}

}






export function removeSourceId() {
	return {
		type: REMOVE_SOURCE_ID
	}
}




export function removeDestinationId() {
	return {
		type: REMOVE_DESTINATION_ID
	}
}







function findOpruut() {
	// console.log('Action: OpruutRequestActions : findOpruut, arguement : () : no argument');

	return {
		type: FIND_OPRUUT
	}
}






function getOpruut(opruut) {
	// console.log('Action: OpruutRequestActions : getOpruut, arguement : (opruut) : ', opruut);

	
	return function(dispatch, getState, Echo) {
		// console.log('Action: OpruutRequestActions : type: dispatch: inside:  fetchOpruutIfNeeded, arguement : no result  ');
		
		// first close leave the Echo channel realted to the received opruut
		Echo.leave(`opruut.request.${opruut.id}`);
		// console.log(`Echo left channel : opruut.${opruut.id}`);

		// let opruut_obj = {[opruut.id]: opruut};
		// now add opruut to result list
		return dispatch(addOpruutResultToOpruutList(opruut));

	};
}




function getOpruutSuccess() {
	// console.log('Action: OpruutRequestActions : getOpruutSuccess, arguement : () : ');

	return {
		type: GET_OPRUUT_SUCCESS
	}
}





function getOpruutFailure(err) {
	// console.log('Action: OpruutRequestActions : findOpruutFailure, arguement : (err) : ', err);

	return {
		type: GET_OPRUUT_FAILURE,
		error: err
	}
}



function incompleteRequestData() {
	// console.log('Action: OpruutRequestActions : incompleteRequestData, arguement : () : no argument');

	return {
		type: INCOMPLETE_REQUEST_DATA
	};
}


function completeRequestData() {
	// console.log('Action: OpruutRequestActions : completeRequestData, arguement : () : no argument');

	return {
		type: COMPLETE_REQUEST_DATA
	};
}



function clearOpruut() {
	// console.log('Action: OpruutRequestActions : clearOpruut, arguement : () : no argument');

	return {
		type: CLEAR_OPRUUT
	}
}



function shouldClearOpruut(opruutRequest) {
	// console.log('Action: OpruutRequestActions : shouldClearOpruut, arguement : (opruutRequest) : ', opruutRequest);	
	
	if (opruutRequest.isFetching) {
		// only allow one parallele request
		// console.log('opruutRequest.isFetching is true, hence returning false');
	    return false;
	} 
	if (opruutRequest.data) {
		// console.log('opruutRequest.data.length is 0, hence returning false');
		return false;
	}

	// console.log('opruutRequest.isFetching is false and opruutRequest.data.length is not 0, hence returning true');
	return true;
}





export function clearOpruutIfNeeded(opruut) {
	// console.log('Action: OpruutRequestActions : clearOpruutIfNeeded, arguement : (opruut) : ', opruut);

	return function(dispatch, getState) {
		// console.log('Action: OpruutRequestActions : type: dispatch: inside:  fetchOpruutIfNeeded, arguement : (getState) : ', getState());
		
		let { opruutRequest } = getState();

		if (shouldClearOpruut(opruutRequest)) {
		    
		    return dispatch(clearOpruut());
		}


	};
}




function fetchOpruut(requestData) {
	// console.log('Action: OpruutRequestActions : type: dispatch : outside : fetchOpruut, arguement : (requestData) : ', requestData);
	
	return function(dispatch, getState, Echo) {
		// console.log('Action: OpruutRequestActions : type: dispatch : inside : fetchOpruut, arguement : dispatch : ');

		dispatch(findOpruut())
		
		axios.post('/api/v1/find/opruut', requestData)
		.then(function (response) {
		    
		    // console.log('response : ',response);
		    let opruut_id = response.data.opruut_id;
		   	
		    Echo.channel(`opruut.request.${opruut_id}`)
		    	.listen('OpruutCalculated', (event) => {
		    		// console.log('OpruutCalculated : event : ', event);
		    		
		    		let opruut = event;

		    		// first dispatch opruut received successfuly 
		    		// so the isfetching is changed
		    		dispatch(getOpruutSuccess());
		    		
		    		// now dipatch the result
		    		return dispatch(getOpruut(opruut));
		    	});
		
		})
		.catch(function (error) {
		    // console.log('ERROR : ', error);
			return dispatch(getOpruutFailure(error));
		});
	
	};

}




function shouldFetchOpruut(opruutRequest, dispatch) {
	// console.log('Action: OpruutRequestActions : shouldFetchOpruut, arguement : (opruutRequest) : ', opruutRequest);	
	
	if (opruutRequest.isFetching) {
		// only allow one parallele request
		// console.log('opruutRequest.isFetching is true, hence returning false');
	    return false;
	} 
	if (opruutRequest.source_id === null || opruutRequest.destination_id === null || opruutRequest.preference === null || opruutRequest.rideTime === null) {
		// console.log('opruutRequest.source_id is false or the others, hence returning false');
		// all the four options are must for the request other send a error informing propblems with the input
		// first dispatch error message regarding inputs necessary
		// console.log('first dispatch error message regarding inputs necessary, incompleteRequestData ');
		dispatch(incompleteRequestData());
		// now return false informing not to to send the request
		// console.log("now returning false from shouldFetchOpruut");
		return false
	}
	// console.log('opruutRequest.isFetching is false, hence returning true');
	// first dispatch success message regarding inputs complete
	// console.log('first dispatch success message regarding inputs necessary, completeRequestData ');
	dispatch(completeRequestData());
	// now return false informing not to to send the request
	// console.log("now returning true from shouldFetchOpruut");
	return true;
}





export function fetchOpruutIfNeeded() {
	// console.log('Action: OpruutRequestActions : type: dispatch: outside:  fetchOpruutIfNeeded, arguement : (requestData) : ');
	
	return function(dispatch, getState) {
		// console.log('Action: OpruutRequestActions : type: dispatch: inside:  fetchOpruutIfNeeded, arguement : (getState) : ', getState());
		
		let { opruutRequest } = getState();

		if (shouldFetchOpruut(opruutRequest, dispatch)) {

			let requestData = {
				source_id: opruutRequest.source_id,
				destination_id: opruutRequest.destination_id,
				preference: opruutRequest.preference,
				ride_time: opruutRequest.rideTime,
				source: opruutRequest.source_name,
				destination: opruutRequest.destination_name
			}
		    
		    return dispatch(fetchOpruut(requestData));
		}


	};

}