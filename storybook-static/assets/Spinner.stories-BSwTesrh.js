import{j as e}from"./jsx-runtime-u17CrQMm.js";import{c as o}from"./index-LHNt3CwB.js";import{c}from"./utils-BQHNewu7.js";import{L as p}from"./loader-circle-sKsh7iHY.js";import"./createLucideIcon-Cq8_ABKM.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";const x=o("animate-spin",{variants:{variant:{default:"text-primary",muted:"text-muted-foreground",white:"text-white",inherit:"text-current"},size:{xs:"size-3",sm:"size-4",md:"size-6",lg:"size-8",xl:"size-12"}},defaultVariants:{variant:"default",size:"md"}});function s({variant:a,size:m,className:l,label:d="Loading..."}){return e.jsxs("span",{"data-slot":"spinner",role:"status","aria-label":d,children:[e.jsx(p,{className:c(x({variant:a,size:m}),l),"aria-hidden":"true"}),e.jsx("span",{className:"sr-only",children:d})]})}s.__docgenInfo={description:"",methods:[],displayName:"Spinner",props:{className:{required:!1,tsType:{name:"string"},description:""},label:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:'"Loading..."',computed:!1}}},composes:["VariantProps"]};const j={title:"Feedback/Spinner",component:s,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{size:{control:"select",options:["xs","sm","md","lg","xl"]},variant:{control:"select",options:["default","muted","white","inherit"]}}},t={args:{size:"md",variant:"default"}},r={render:()=>e.jsx("div",{className:"flex items-end gap-4",children:["xs","sm","md","lg","xl"].map(a=>e.jsxs("div",{className:"flex flex-col items-center gap-1.5",children:[e.jsx(s,{size:a}),e.jsx("span",{className:"text-xs text-muted-foreground",children:a})]},a))})},n={render:()=>e.jsxs("div",{className:"flex flex-wrap gap-6",children:[e.jsxs("div",{className:"flex flex-col items-center gap-1.5",children:[e.jsx(s,{variant:"default",size:"md"}),e.jsx("span",{className:"text-xs text-muted-foreground",children:"default"})]}),e.jsxs("div",{className:"flex flex-col items-center gap-1.5",children:[e.jsx(s,{variant:"muted",size:"md"}),e.jsx("span",{className:"text-xs text-muted-foreground",children:"muted"})]}),e.jsxs("div",{className:"flex flex-col items-center gap-1.5 rounded bg-primary p-2",children:[e.jsx(s,{variant:"white",size:"md"}),e.jsx("span",{className:"text-xs text-primary-foreground",children:"white"})]})]})},i={render:()=>e.jsxs("button",{className:"inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground",disabled:!0,children:[e.jsx(s,{size:"sm",variant:"white"}),"Saving…"]})};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    size: 'md',
    variant: 'default'
  }
}`,...t.parameters?.docs?.source}}};r.parameters={...r.parameters,docs:{...r.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex items-end gap-4">
            {(['xs', 'sm', 'md', 'lg', 'xl'] as const).map(size => <div key={size} className="flex flex-col items-center gap-1.5">
                    <Spinner size={size} />
                    <span className="text-xs text-muted-foreground">{size}</span>
                </div>)}
        </div>
}`,...r.parameters?.docs?.source}}};n.parameters={...n.parameters,docs:{...n.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex flex-wrap gap-6">
            <div className="flex flex-col items-center gap-1.5">
                <Spinner variant="default" size="md" />
                <span className="text-xs text-muted-foreground">default</span>
            </div>
            <div className="flex flex-col items-center gap-1.5">
                <Spinner variant="muted" size="md" />
                <span className="text-xs text-muted-foreground">muted</span>
            </div>
            <div className="flex flex-col items-center gap-1.5 rounded bg-primary p-2">
                <Spinner variant="white" size="md" />
                <span className="text-xs text-primary-foreground">white</span>
            </div>
        </div>
}`,...n.parameters?.docs?.source}}};i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  render: () => <button className="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground" disabled>
            <Spinner size="sm" variant="white" />
            Saving…
        </button>
}`,...i.parameters?.docs?.source}}};const y=["Default","AllSizes","AllVariants","InButton"];export{r as AllSizes,n as AllVariants,t as Default,i as InButton,y as __namedExportsOrder,j as default};
