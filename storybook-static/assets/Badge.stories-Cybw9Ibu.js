import{j as e}from"./jsx-runtime-u17CrQMm.js";import{B as a}from"./badge-dtsZ3OQs.js";import"./index-yvwtsnL6.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-dmJXtalC.js";import"./index-LHNt3CwB.js";import"./utils-BQHNewu7.js";const p={title:"Data Display/Badge",component:a,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{variant:{control:"select",options:["default","secondary","destructive","outline","success","warning","info"]},children:{control:"text"}}},r={args:{children:"Badge",variant:"default"}},n={render:()=>e.jsxs("div",{className:"flex flex-wrap gap-2",children:[e.jsx(a,{variant:"default",children:"Default"}),e.jsx(a,{variant:"secondary",children:"Secondary"}),e.jsx(a,{variant:"destructive",children:"Destructive"}),e.jsx(a,{variant:"outline",children:"Outline"}),e.jsx(a,{variant:"success",children:"Success"}),e.jsx(a,{variant:"warning",children:"Warning"}),e.jsx(a,{variant:"info",children:"Info"})]})},t={render:()=>e.jsxs("div",{className:"flex items-center gap-2",children:[e.jsx("span",{className:"text-sm font-medium",children:"Status"}),e.jsx(a,{variant:"success",children:"Active"})]})};r.parameters={...r.parameters,docs:{...r.parameters?.docs,source:{originalSource:`{
  args: {
    children: 'Badge',
    variant: 'default'
  }
}`,...r.parameters?.docs?.source}}};n.parameters={...n.parameters,docs:{...n.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex flex-wrap gap-2">
            <Badge variant="default">Default</Badge>
            <Badge variant="secondary">Secondary</Badge>
            <Badge variant="destructive">Destructive</Badge>
            <Badge variant="outline">Outline</Badge>
            <Badge variant="success">Success</Badge>
            <Badge variant="warning">Warning</Badge>
            <Badge variant="info">Info</Badge>
        </div>
}`,...n.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex items-center gap-2">
            <span className="text-sm font-medium">Status</span>
            <Badge variant="success">Active</Badge>
        </div>
}`,...t.parameters?.docs?.source}}};const g=["Default","AllVariants","InContext"];export{n as AllVariants,r as Default,t as InContext,g as __namedExportsOrder,p as default};
