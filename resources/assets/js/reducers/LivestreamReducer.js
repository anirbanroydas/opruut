import { REQUEST_LIVESTREAM, LIVESTREAM_SUBSCRIBED, RECEIVE_LIVESTREAMS, RECEIVE_LIVESTREAM, RECEIVE_LIVESTREAM_FAILURE } from '../actions';


const initialState =  {
	data: [],
	isSubscribed: false,
	error: null,
	isFetching: false
};


export default function livestream(state = initialState, action) {
	
	switch (action.type) {
	
	case REQUEST_LIVESTREAM:
		return {
			...state, 
			isFetching: true,
		};


	case LIVESTREAM_SUBSCRIBED:
		return {
			...state, 
			isSubscribed: true,
		};


	
	case RECEIVE_LIVESTREAMS:
		return {
			...state, 
			isFetching: false,
			data: [...action.data, ...state.data],
			error: null
		};




	case RECEIVE_LIVESTREAM:
		return {
			...state, 
			isFetching: false,
			data: [action.data, ...state.data],
			error: null
		};

	case RECEIVE_LIVESTREAM_FAILURE:
      
      	return {
      		...state,
  			isFetching: false,
  			error: action.error
      	};

	default:
		return state;
			
	}

}