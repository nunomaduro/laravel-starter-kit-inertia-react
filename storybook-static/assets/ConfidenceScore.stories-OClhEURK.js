import{j as e}from"./jsx-runtime-u17CrQMm.js";import{c as a}from"./utils-BQHNewu7.js";const m={sm:"h-1 text-xs",md:"h-1.5 text-sm",lg:"h-2 text-base"};function g(s){return s>=.8?"bg-success":s>=.5?"bg-warning":"bg-error"}function r({score:s,showLabel:u=!0,size:d="md",className:p}){const i=Math.min(Math.max(s,0),1),l=Math.round(i*100);return e.jsxs("div",{className:a("space-y-1",p),children:[u&&e.jsxs("div",{className:"flex items-center justify-between",children:[e.jsx("span",{className:a("font-medium text-muted-foreground",m[d]),children:"Confidence"}),e.jsxs("span",{className:a("font-semibold tabular-nums",m[d]),children:[l,"%"]})]}),e.jsx("div",{className:a("w-full rounded-full bg-muted overflow-hidden",m[d]),children:e.jsx("div",{className:a("h-full rounded-full transition-all duration-500",g(i)),style:{width:`${l}%`},role:"meter","aria-valuenow":i,"aria-valuemin":0,"aria-valuemax":1,"aria-label":`Confidence ${l}%`})})]})}r.__docgenInfo={description:`Horizontal progress bar indicating an AI confidence score (0–1).
Color transitions from red → amber → green based on the score.`,methods:[],displayName:"ConfidenceScore",props:{score:{required:!0,tsType:{name:"number"},description:"Value between 0 and 1."},showLabel:{required:!1,tsType:{name:"boolean"},description:"Show the numeric label.",defaultValue:{value:"true",computed:!1}},size:{required:!1,tsType:{name:"union",raw:"'sm' | 'md' | 'lg'",elements:[{name:"literal",value:"'sm'"},{name:"literal",value:"'md'"},{name:"literal",value:"'lg'"}]},description:"Visual size preset.",defaultValue:{value:"'md'",computed:!1}},className:{required:!1,tsType:{name:"string"},description:""}}};const h={title:"AI/ConfidenceScore",component:r,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{score:{control:{type:"range",min:0,max:1,step:.01}},showLabel:{control:"boolean"},size:{control:"select",options:["sm","md","lg"]}}},o={args:{score:.92,showLabel:!0,size:"md"},render:s=>e.jsx("div",{className:"w-64",children:e.jsx(r,{...s})})},n={args:{score:.64,showLabel:!0,size:"md"},render:s=>e.jsx("div",{className:"w-64",children:e.jsx(r,{...s})})},t={args:{score:.31,showLabel:!0,size:"md"},render:s=>e.jsx("div",{className:"w-64",children:e.jsx(r,{...s})})},c={render:()=>e.jsx("div",{className:"space-y-4 w-64",children:["sm","md","lg"].map(s=>e.jsxs("div",{className:"space-y-1",children:[e.jsx("p",{className:"text-xs text-muted-foreground",children:s}),e.jsx(r,{score:.78,size:s})]},s))})};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    score: 0.92,
    showLabel: true,
    size: 'md'
  },
  render: args => <div className="w-64"><ConfidenceScore {...args} /></div>
}`,...o.parameters?.docs?.source}}};n.parameters={...n.parameters,docs:{...n.parameters?.docs,source:{originalSource:`{
  args: {
    score: 0.64,
    showLabel: true,
    size: 'md'
  },
  render: args => <div className="w-64"><ConfidenceScore {...args} /></div>
}`,...n.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    score: 0.31,
    showLabel: true,
    size: 'md'
  },
  render: args => <div className="w-64"><ConfidenceScore {...args} /></div>
}`,...t.parameters?.docs?.source}}};c.parameters={...c.parameters,docs:{...c.parameters?.docs,source:{originalSource:`{
  render: () => <div className="space-y-4 w-64">
            {(['sm', 'md', 'lg'] as const).map(size => <div key={size} className="space-y-1">
                    <p className="text-xs text-muted-foreground">{size}</p>
                    <ConfidenceScore score={0.78} size={size} />
                </div>)}
        </div>
}`,...c.parameters?.docs?.source}}};const v=["High","Medium","Low","Sizes"];export{o as High,t as Low,n as Medium,c as Sizes,v as __namedExportsOrder,h as default};
