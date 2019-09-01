import { registerPlugin } from '@wordpress/plugins'
import { Fragment } from '@wordpress/element' 
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post'
import { Panel } from '@wordpress/components'
import { AttachmentField } from './component/attachment.field'

registerPlugin('inky', {
  icon: 'admin-customizer',
  render: () => 
    <Fragment>
      <PluginSidebarMoreMenuItem
        target="inky-sidebar"
        icon="admin-customizer"
      >
        Inky
      </PluginSidebarMoreMenuItem>
      <PluginSidebar
        name = "inky-sidebar"
        icom = "admin-post"
        title = "Inky"
      >
        <Panel>
          <AttachmentField metaKey="inky_webcomic" />
        </Panel>
      </PluginSidebar>
    </Fragment>
})
