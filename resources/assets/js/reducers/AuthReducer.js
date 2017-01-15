import { AUTH_USER, AUTH_ERROR, SIGN_OUT_USER, UPDATE_GLOBAL_REQUESTS } from '../actions';


const initialState =  {
	authenticated: false,
	userinfo: null,
	globalRequests: 0,
	error: null
};

export default function auth(state = initialState, action) {
	
	switch (action.type) {
	
	case UPDATE_GLOBAL_REQUESTS:
		return {
			...state, 
			globalRequests: action.globalRequests
		};


	case AUTH_USER:
		return {
			...state,
			authenticated: true,
			userinfo: action.userinfo,
			error: null
		};
	
	case SIGN_OUT_USER:
		// add a avatar link otherwise
		// immediately the avatar will be needed by props
		// and will throw a warning/error in react
		let avatar = state.userinfo.avatar;
		let newUserinfo = {avatar: avatar};
		return {
			...state,
			authenticated: false,
			userinfo: newUserinfo,
			error: null
		};

	case AUTH_ERROR:
		return {
			...state,
			error: action.error
		};
	
	default:
		return state;
	}
}