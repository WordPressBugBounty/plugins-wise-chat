import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { sendMessage } from "actions/messages";
import { alertError, alertInfo, toastInfo, toastError, cancelMessageReplyTo } from "actions/ui";
import Attachments from './Attachments';
import EmoticonsPopup from "./EmoticonsPopup";
import Decorator from "ui/common/plain/Decorator";
import HtmlInput from "./HtmlInput";
import ProgressBar from "ui/common/plain/ProgressBar";

class InputRich extends React.Component {

	constructor(props) {
		super(props);

		this.state = {
			message: '',
			messageChunk: '',
			imageSource: undefined,
			fileSource: undefined,
			attachmentsProcessing: undefined,
			attachments: []
		}

		this.inputRef = React.createRef();
		this.sendMessage = this.sendMessage.bind(this);
		this.handleFileChange = this.handleFileChange.bind(this);
		this.handleImageChange = this.handleImageChange.bind(this);
		this.handleSoundInputChange = this.handleSoundInputChange.bind(this);
		this.handleEmoticonSelect = this.handleEmoticonSelect.bind(this);
		this.handleCancelReplyTo = this.handleCancelReplyTo.bind(this);

		this.handleHtmlInputChange = this.handleHtmlInputChange.bind(this);
	}

	componentDidUpdate(prevProps) {
		const postedMessageSuccess = this.props.postedMessage !== prevProps.postedMessage && this.props.postedMessage.success === true;
		const postedMessageFailure = this.props.postedMessage !== prevProps.postedMessage && this.props.postedMessage.success === false;
		const inputAppendChanged = this.props.uiChannel !== prevProps.uiChannel && this.props.uiChannel && prevProps.uiChannel && this.props.uiChannel.inputAppend !== prevProps.uiChannel.inputAppend;
		const replyToChanged = this.props.replyTo !== prevProps.replyTo && this.props.replyTo;

		if (postedMessageSuccess) {
			this.setState({ attachments: [] });
			this.props.cancelMessageReplyTo(this.props.channel.id);
			if (this.props.postedMessage.result && this.props.postedMessage.result.message && this.props.postedMessage.result.message.hidden === true) {
				this.props.toastInfo(this.props.i18n.approvalWarning);
			}
		}
		if (postedMessageFailure) {
			this.props.alertError(this.props.postedMessage.error ? this.props.postedMessage.error : 'Error #i1');
		}
		if (inputAppendChanged) {
			this.setState({messageChunk: this.props.uiChannel.inputAppend });
		}
		if (replyToChanged) {
			this.inputRef.focus();
		}
	}

	sendMessage(e) {
		if (this.state.message.length > 0 || this.state.attachments.length > 0) {
			this.props.sendMessage(this.state.message, this.state.attachments, { replyToMessageId: this.props.replyTo }, this.props.channel.id);
			this.setState({message: ''});
		}
	}

	handleFileChange(e) {
		this.setState({ fileSource: e.target.files.length > 0 ? [e.target.files[0]] : [] }, function() { e.target.value = ''; });
	}

	handleImageChange(e) {
		this.setState({ imageSource: e.target.files.length > 0 ? [e.target.files[0]] : [] }, function() { e.target.value = ''; });
	}

	handleSoundInputChange(sound) {
		this.setState({ soundSource: sound });
	}

	handleEmoticonSelect(emoticon) {
		this.setState({ messageChunk: emoticon });
	}

	handleHtmlInputChange(value) {
		this.setState({ message: value, messageChunk: '' });
	}

	handleCancelReplyTo(e) {
		e.preventDefault();

		this.props.cancelMessageReplyTo(this.props.channel.id);
	}

	render() {
		if (this.props.channel.type === 'direct' && this.props.channel.readOnly) {
			return (
				<div className="wcChannelInput">
					<div className="wcReadOnlyDirectChannel wcErrorBox">
						<Decorator>{ this.props.i18n.notAllowedToSendDirectMessages }</Decorator>
					</div>
				</div>
			)
		}
		if (this.props.channel.type === 'public' && this.props.channel.readOnly) {
			return null;
		}
		const replyToMessage = this.props.replyTo && this.props.channelMessages ? this.props.channelMessages.find( message => message.id === this.props.replyTo ) : undefined;

		return(
			<div className="wcChannelInput">
				{ this.props.configuration.interface.input.userName && !replyToMessage &&
					<div className="wcCurrentUserName">
						{ this.props.application.user.name }:
					</div>
				}
				{ replyToMessage &&
					<div className="wcReplyTo">
						<div className="wcName">{ this.props.i18n.replyingTo } { replyToMessage.sender.name }</div>
						<div className="wcContent"><Decorator>{ replyToMessage.text }</Decorator></div>
						<a className="wcDeleteButton wcFunctional" onClick={ this.handleCancelReplyTo } />
					</div>
				}
				<div className="wcInputs">
					<HtmlInput
						ref={ ref => this.inputRef = ref }
						placeholder={ this.props.postedMessage.inProgress ? this.props.i18nBase.sending : this.props.i18nBase.hint }
						inputRequest={ this.state.messageChunk }
						resetRequest={ this.state.message === '' }
						onChange={ this.handleHtmlInputChange }
						onSendingRequest={ this.sendMessage }
					/>

					<div className="wcInputButtons">

						{this.props.configuration.interface.input.emoticons.enabled &&
							<EmoticonsPopup onSelect={ this.handleEmoticonSelect } />
						}

						{this.props.configuration.interface.input.images.enabled &&
							<div className="wcInputButton wcImageAttachment" title={ this.props.i18n.uploadPicture }>
								<input
									type="file"
									accept="image/*;capture=camera"
									title={ this.props.i18n.uploadPicture }
									onChange={ this.handleImageChange }
								/>
							</div>
						}

						{this.props.configuration.interface.input.attachments.enabled &&
							<div className="wcInputButton wcFileAttachment" title={ this.props.i18n.attachFile }>
								<input
									type="file"
									accept={ this.props.configuration.interface.input.attachments.extensionsList }
									title={ this.props.i18n.attachFile }
									onChange={ this.handleFileChange }
								/>
							</div>
						}
						{ this.props.configuration.interface.input.submit &&
							<button
								className="wcSubmit"
								onClick={ this.sendMessage }
								disabled={ this.props.postedMessage.inProgress }
							>
								{ this.props.i18nBase.send }
							</button>
						}
					</div>
				</div>

				{this.state.attachments.length > 0 &&
					<ProgressBar visible={ this.props.postedMessage.inProgress } progress={ this.props.postedMessage.progress } />
				}
				<Attachments
					processingMessage={ this.state.attachmentsProcessing }
					channel={ this.props.channel }
					imageSource={ this.state.imageSource }
					fileSource={ this.state.fileSource }
					soundSource={ this.state.soundSource }
					attachments={ this.state.attachments }
					onChange={ attachments => this.setState({ attachments: attachments }) }
				/>
			</div>
		)
	}

}

InputRich.propTypes = {
	channel: PropTypes.object.isRequired,
	configuration: PropTypes.object.isRequired,
	channelMessages: PropTypes.array,
	postedMessage: PropTypes.object,
	replyTo: PropTypes.string
};

export default connect(
	(state, ownProps) => ({
		configuration: state.configuration,
		application: state.application,
		i18nBase: state.configuration.i18n,
		i18n: state.application.i18n,
		channelMessages: state.messages.received[ownProps.channel.id],
		postedMessage: state.messages.posted[ownProps.channel.id] || {},
		uiChannel: state.ui.channels[ownProps.channel.id],
		replyTo: state.ui.replyToMessages[ownProps.channel.id]
	}),
	{ sendMessage, alertError, alertInfo, toastInfo, toastError, cancelMessageReplyTo }
)(InputRich);