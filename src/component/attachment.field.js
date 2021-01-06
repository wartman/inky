import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor'
import { PanelBody, Button, Spinner, ResponsiveWrapper } from '@wordpress/components'

const ALLOWED_MEDIA_TYPES = [ 'image' ]

export const AttachmentField = compose(

  withSelect((select, props) => {
    const { getMedia } = select('core')
    const { getEditedPostAttribute } = select('core/editor')
    const meta = getEditedPostAttribute('meta')
    const imageId = meta ? meta[props.metaKey] : null
    return {
      media: imageId ? getMedia(imageId) : null,
      imageId,
    }
  }),

  withDispatch((dispatch, props) => {
    return {
      onUpdateImage( image ) {
        dispatch('core/editor').editPost({
          meta: { [props.metaKey]: image.id }
        });
      },
      onRemoveImage() {
        dispatch('core/editor').editPost({
          meta: { [props.metaKey]: 0 }
        });
      },
    }
  })

)(({ imageId, onUpdateImage, onRemoveImage, media }) => {
  let mediaWidth
  let mediaHeight = 200
  let mediaSourceUrl

  if ( media ) {
    mediaWidth = media.media_details.width
    mediaHeight = media.media_details.height
    mediaSourceUrl = media.source_url
  }

  const instructions = <p>You can't</p>

  // todo: loading and stuff
  return (
    <PanelBody 
      title="Add Attachment"
      initialOpen={true}
    >
      <MediaUploadCheck fallback={ instructions }>
        <MediaUpload
          title="Webcomic"
          onSelect={onUpdateImage}
          allowedTypes={ALLOWED_MEDIA_TYPES}
          modalClass='editor-post-inky-image__media-modal'
          multiple={false}
          value={imageId}
          render={({ open }) => (
            <Button
              className={ !imageId ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview' }
              onClick={ open }
              aria-label={ !imageId ? null : 'Edit or update the image' }
            >
              { !!imageId && !media && <Spinner /> }
              { !!imageId && media &&
                <ResponsiveWrapper
                  naturalWidth={ mediaWidth }
                  naturalHeight={ mediaHeight }
                >
                  <img src={ mediaSourceUrl } alt="" />
                </ResponsiveWrapper>
              }
              { !imageId && (<span>Set Webcomic</span>) }
            </Button>
          )}
        />
      </MediaUploadCheck>
      {!!imageId &&
        <MediaUploadCheck>
          <Button onClick={ onRemoveImage } isLink isDestructive>
            Remove Webcomic
          </Button>
        </MediaUploadCheck>
      }
    </PanelBody>
  )
})
