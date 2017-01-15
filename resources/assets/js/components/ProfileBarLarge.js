import React from 'react';
import classnames from 'classnames';

import Avatar from 'material-ui/Avatar';

import profileBarLargeStyles from '../../sass/components/profileBarLarge.scss';



class ProfileBarLarge extends React.Component {


	getUserName() {

		if (this.props.authenticated) {
			return this.props.userinfo.name;
		}

		return 'Hi Buddy!'

	}


	getUserUsername() {

		if (this.props.authenticated) {
			return this.props.userinfo.username;
		}

		return 'iHaventRegistered'

	}


	getRequestNumber() {

		return this.props.globalRequests;

	}




	render() {
	
		return (
				
			<div className={ classnames('jumbotron', profileBarLargeStyles.jumbotron, profileBarLargeStyles.profileWelcomeItem) } >
							
				<div className={ classnames('media') } >
					<div className={ classnames('media-left', 'media-middle') } >
						<div className={ classnames('media-object') } >
					    	<Avatar 
								src={ this.props.userinfo.avatar ? this.props.userinfo.avatar : "media/avatars/avatar_male.jpg" }
								size={220} 
								className={ classnames(profileBarLargeStyles.profilePageAvatarThumb) }  
							/>
					    </div>
					</div>

			        <div className={ classnames('media-body', 'media-middle') } >
			        	<div className={ classnames('meadia-heading', profileBarLargeStyles.profileWelcomeItemTitle) } >
				          <a href="#" className={ classnames(profileBarLargeStyles.profileWelcomeItemName) } > { this.getUserName() } </a>
				          
				        </div>

				        <div className={ classnames(profileBarLargeStyles.profileWelcomeItemSubtitle) } >
				            <a href="#" className={ classnames(profileBarLargeStyles.profileWelcomeItemUsername) } >{ `@${this.getUserUsername()}` }</a>
				        </div>


				        <div className={ classnames(profileBarLargeStyles.profileWelcomeItemFollow) } >
				            <div className={ classnames(profileBarLargeStyles.lead3) } >
			    				<button id="follow" className={ classnames('btn', 'btn-default', 'btn-md', 'center-block') }  role="button"  >
			    					{ `${this.getRequestNumber()} Requests` }
			    				</button>
		    				</div>
				        </div>
			        </div>
				</div>
			</div>
			
			
		);

	}


}






ProfileBarLarge.propTypes = {

	authenticated: React.PropTypes.bool,
	userinfo: React.PropTypes.object,
	globalRequests: React.PropTypes.number

};




export default ProfileBarLarge;

