import{j as e}from"./jsx-runtime-u17CrQMm.js";import{T as o,a as d,b as r,c as t}from"./tabs-C5KJ5DR1.js";import"./index-LHNt3CwB.js";import"./utils-BQHNewu7.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-CFnpPvE6.js";import"./index-BMy6K8Gt.js";import"./index-BMkthtSq.js";import"./index-dmJXtalC.js";import"./index-BZKZbRGl.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-DegZLnM5.js";import"./index-CfYvYFbm.js";const N={title:"Navigation/Tabs",component:o,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{defaultValue:{control:"text"}}},s={render:()=>e.jsxs(o,{defaultValue:"overview",className:"w-96",children:[e.jsxs(d,{children:[e.jsx(r,{value:"overview",children:"Overview"}),e.jsx(r,{value:"analytics",children:"Analytics"}),e.jsx(r,{value:"reports",children:"Reports"})]}),e.jsx(t,{value:"overview",className:"p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground",children:"Overview content goes here."}),e.jsx(t,{value:"analytics",className:"p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground",children:"Analytics content goes here."}),e.jsx(t,{value:"reports",className:"p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground",children:"Reports content goes here."})]})},a={render:()=>e.jsxs(o,{defaultValue:"active",className:"w-96",children:[e.jsxs(d,{children:[e.jsx(r,{value:"active",children:"Active"}),e.jsx(r,{value:"disabled",disabled:!0,children:"Disabled"}),e.jsx(r,{value:"other",children:"Other"})]}),e.jsx(t,{value:"active",className:"p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground",children:"Active tab is selected."}),e.jsx(t,{value:"other",className:"p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground",children:"Other tab content."})]})};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  render: () => <Tabs defaultValue="overview" className="w-96">
            <TabsList>
                <TabsTrigger value="overview">Overview</TabsTrigger>
                <TabsTrigger value="analytics">Analytics</TabsTrigger>
                <TabsTrigger value="reports">Reports</TabsTrigger>
            </TabsList>
            <TabsContent value="overview" className="p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground">
                Overview content goes here.
            </TabsContent>
            <TabsContent value="analytics" className="p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground">
                Analytics content goes here.
            </TabsContent>
            <TabsContent value="reports" className="p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground">
                Reports content goes here.
            </TabsContent>
        </Tabs>
}`,...s.parameters?.docs?.source}}};a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:`{
  render: () => <Tabs defaultValue="active" className="w-96">
            <TabsList>
                <TabsTrigger value="active">Active</TabsTrigger>
                <TabsTrigger value="disabled" disabled>Disabled</TabsTrigger>
                <TabsTrigger value="other">Other</TabsTrigger>
            </TabsList>
            <TabsContent value="active" className="p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground">
                Active tab is selected.
            </TabsContent>
            <TabsContent value="other" className="p-4 border border-border rounded-md mt-2 text-sm text-muted-foreground">
                Other tab content.
            </TabsContent>
        </Tabs>
}`,...a.parameters?.docs?.source}}};const w=["Default","WithDisabled"];export{s as Default,a as WithDisabled,w as __namedExportsOrder,N as default};
