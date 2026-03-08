import{j as e}from"./jsx-runtime-u17CrQMm.js";import{P as x}from"./progress-NBkSIhKh.js";import{c}from"./utils-BQHNewu7.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-dmJXtalC.js";function a({used:s,limit:o,label:d,unit:p="seats",className:g}){const r=o>0?Math.min(s/o*100,100):0,u=r>=80&&r<100,m=r>=100;return e.jsxs("div",{className:c("space-y-1.5",g),children:[e.jsxs("div",{className:"flex items-center justify-between text-sm",children:[d&&e.jsx("span",{className:"text-muted-foreground",children:d}),e.jsxs("span",{className:c("font-medium tabular-nums",m?"text-destructive":u?"text-warning":"text-foreground"),children:[s,"/",o," ",p]})]}),e.jsx(x,{value:r,className:c("h-2",m?"[&>[data-slot=progress-indicator]]:bg-destructive":u?"[&>[data-slot=progress-indicator]]:bg-warning":"")}),m&&e.jsx("p",{className:"text-xs text-destructive",children:"You have reached your limit."})]})}a.__docgenInfo={description:"",methods:[],displayName:"UsageMeter",props:{used:{required:!0,tsType:{name:"number"},description:""},limit:{required:!0,tsType:{name:"number"},description:""},label:{required:!1,tsType:{name:"string"},description:""},unit:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:'"seats"',computed:!1}},className:{required:!1,tsType:{name:"string"},description:""}}};const T={title:"SaaS/UsageMeter",component:a,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{used:{control:{type:"range",min:0,max:100}},limit:{control:{type:"number"}},label:{control:"text"},unit:{control:"text"}}},t={args:{used:5,limit:10,label:"Team seats",unit:"seats"},render:s=>e.jsx("div",{className:"w-72",children:e.jsx(a,{...s})})},i={args:{used:9,limit:10,label:"Team seats",unit:"seats"},render:s=>e.jsx("div",{className:"w-72",children:e.jsx(a,{...s})})},n={args:{used:10,limit:10,label:"Team seats",unit:"seats"},render:s=>e.jsx("div",{className:"w-72",children:e.jsx(a,{...s})})},l={render:()=>e.jsxs("div",{className:"space-y-6 w-80",children:[e.jsx(a,{used:5,limit:10,label:"Team seats",unit:"seats"}),e.jsx(a,{used:4800,limit:5e3,label:"API calls",unit:"calls"}),e.jsx(a,{used:2400,limit:2500,label:"Storage",unit:"MB"})]})};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    used: 5,
    limit: 10,
    label: 'Team seats',
    unit: 'seats'
  },
  render: args => <div className="w-72"><UsageMeter {...args} /></div>
}`,...t.parameters?.docs?.source}}};i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  args: {
    used: 9,
    limit: 10,
    label: 'Team seats',
    unit: 'seats'
  },
  render: args => <div className="w-72"><UsageMeter {...args} /></div>
}`,...i.parameters?.docs?.source}}};n.parameters={...n.parameters,docs:{...n.parameters?.docs,source:{originalSource:`{
  args: {
    used: 10,
    limit: 10,
    label: 'Team seats',
    unit: 'seats'
  },
  render: args => <div className="w-72"><UsageMeter {...args} /></div>
}`,...n.parameters?.docs?.source}}};l.parameters={...l.parameters,docs:{...l.parameters?.docs,source:{originalSource:`{
  render: () => <div className="space-y-6 w-80">
            <UsageMeter used={5} limit={10} label="Team seats" unit="seats" />
            <UsageMeter used={4800} limit={5000} label="API calls" unit="calls" />
            <UsageMeter used={2400} limit={2500} label="Storage" unit="MB" />
        </div>
}`,...l.parameters?.docs?.source}}};const w=["Normal","Warning","AtLimit","Multiple"];export{n as AtLimit,l as Multiple,t as Normal,i as Warning,w as __namedExportsOrder,T as default};
