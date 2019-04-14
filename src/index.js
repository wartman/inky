import { AttachmentField } from './component/attachment.field'

const { Fragment } = wp.element 
const { registerPlugin } = wp.plugins
const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost
const { Panel } = wp.components

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
