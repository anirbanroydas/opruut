import { FIND_OPRUUT, GET_OPRUUT, GET_OPRUUT_SUCCESS, GET_OPRUUT_FAILURE, INCOMPLETE_REQUEST_DATA, 
	COMPLETE_REQUEST_DATA, CLEAR_OPRUUT, UPDATE_PREFERENCE, UPDATE_RIDE_TIME, 
	UPDATE_SOURCE_STATION_ID, UPDATE_SOURCE_STATION_NAME, UPDATE_DESTINATION_STATION_ID, UPDATE_DESTINATION_STATION_NAME,
	REMOVE_SOURCE_ID, REMOVE_DESTINATION_ID, NO_PREFERENCE, RIDE_NOW } from '../actions';


const initialState =  {
	source_id: null,
	destination_id: null,
	source_name: null,
	destination_name: null,
	preference: 1,
	rideTime: RIDE_NOW,
	isIncompleteRequest: false,
	data: null,
	isFetching: false,
	error: null
};



export default function opruutRequest(state = initialState, action) {
	
	switch (action.type) {
	
	case FIND_OPRUUT:
		return {
			...state, 
			isFetching: true,
			error: null
		};


	case GET_OPRUUT:
		return {
			...state, 
			isFetching: false,
			data: action.data,
			error: null			
		};


	case GET_OPRUUT_SUCCESS:
		return {
			...state, 
			isFetching: false,
			error: null			
		};


	case GET_OPRUUT_FAILURE:
      
      	return {
      		...state,
  			isFetching: false,
  			error: action.error
      	};


    case INCOMPLETE_REQUEST_DATA:
		return {
			...state, 
			isIncompleteRequest: true
		};


	case COMPLETE_REQUEST_DATA:
		return {
			...state, 
			isIncompleteRequest: false
		};  	



    case CLEAR_OPRUUT:
		return {
			...state, 
			data: null
		};

    case UPDATE_PREFERENCE:
      
      	return {
      		...state,
  			preference: action.preference,
      	};


    case UPDATE_RIDE_TIME:
      
      	return {
      		...state,
  			rideTime: action.rideTime,
      	};

    case UPDATE_SOURCE_STATION_ID:
      
      	return {
      		...state,
  			source_id: action.source_id,
      	};


    case UPDATE_SOURCE_STATION_NAME:
      
      	return {
      		...state,
  			source_name: action.name,
      	};



    case UPDATE_DESTINATION_STATION_ID:
      
      	return {
      		...state,
  			destination_id: action.destination_id,
      	};


    case UPDATE_DESTINATION_STATION_NAME:
      
      	return {
      		...state,
  			destination_name: action.name,
      	};


    case REMOVE_SOURCE_ID:
      
      	return {
      		...state,
  			source_id: null,
      	};


    case REMOVE_DESTINATION_ID:
      
      	return {
      		...state,
  			destination_id: null,
      	};


	default:
		return state;
			
	}

}