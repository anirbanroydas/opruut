import { UPDATE_NAVBAR_QUICK_LINK } from '../actions';


const initialState =  {
	quicklink: '/favorites'
};

export default function auth(state = initialState, action) {
	
	switch (action.type) {
	
	case UPDATE_NAVBAR_QUICK_LINK:
		return {
			...state, 
			quicklink: action.quicklink
		};
	
	default:
		return state;
	}
}