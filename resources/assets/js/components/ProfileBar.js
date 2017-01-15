import React from 'react';
import classnames from 'classnames';

import Avatar from 'material-ui/Avatar';

import profileBarStyles from '../../sass/components/profileBar.scss';



class ProfileBar extends React.Component {


	getUserAvatar() {

		return (		
			<img 
				src={ this.props.userinfo.avatar ? this.props.userinfo.avatar : "media/avatars/avatar_male.jpg"} 
				alt="pofile photo" 
				className={ classnames('img-responsive', 
					'img-circle', 
					profileBarStyles.homePageAvatarThumb
				) } 
			/>
		);

	}



	getUserName() {

		if (this.props.authenticated) {
			return this.props.userinfo.name;
		}

		return 'Hi Buddy'

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
			
			<div >
				<div className={ classnames(profileBarStyles.homePageProfileInfoWrapper) } >
					<div className={ classnames(profileBarStyles.homePageProfileAvatarWrapper) } >
						<Avatar 
							src={ this.props.userinfo.avatar ? this.props.userinfo.avatar : "media/avatars/avatar_male.jpg"} 
							size={180} 
							className={ classnames(profileBarStyles.homePageAvatarThumb ) }  
						/>
					</div>

					<div className={ classnames(profileBarStyles.homePageProfileItemTitle) } >
			          	<a href="#" className={ classnames(profileBarStyles.homePageProfileItemName) } >
			          		{ this.getUserName() }
			          	</a>
			        </div>

			        <div className={ classnames(profileBarStyles.homePageProfileItemSubtitle) } >
			            <a href="#" className={ classnames(profileBarStyles.homePageProfileItemUsername) } >
			            	{ `@${this.getUserUsername()}` }
			            </a>
			        </div>

			        <div className={ classnames(profileBarStyles.homePageProfileItemInfo) } >
			            <div href="#" className={ classnames(profileBarStyles.profileWelcomeItemFollowers) } >
			            	{ `${this.getRequestNumber()} Requests` }
			            </div>
			        </div>
				</div>
			</div>

		);
	}


}




ProfileBar.propTypes = {

	authenticated: React.PropTypes.bool,
	userinfo: React.PropTypes.object,
	globalRequests: React.PropTypes.number

};



export default ProfileBar;

