import{j as e}from"./jsx-runtime-u17CrQMm.js";import{P as s}from"./progress-NBkSIhKh.js";import"./utils-BQHNewu7.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-dmJXtalC.js";const x={title:"Feedback/Progress",component:s,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{value:{control:{type:"range",min:0,max:100}}}},r={args:{value:60},render:a=>e.jsx(s,{...a,className:"w-80"})},t={args:{value:0},render:a=>e.jsx(s,{...a,className:"w-80"})},l={args:{value:100},render:a=>e.jsx(s,{...a,className:"w-80"})},o={render:()=>e.jsx("div",{className:"space-y-4 w-80",children:[{label:"Storage",value:72},{label:"Memory",value:45},{label:"CPU",value:28},{label:"Bandwidth",value:93}].map(({label:a,value:m})=>e.jsxs("div",{className:"space-y-1.5",children:[e.jsxs("div",{className:"flex justify-between text-sm",children:[e.jsx("span",{className:"text-foreground font-medium",children:a}),e.jsxs("span",{className:"text-muted-foreground",children:[m,"%"]})]}),e.jsx(s,{value:m})]},a))})};r.parameters={...r.parameters,docs:{...r.parameters?.docs,source:{originalSource:`{
  args: {
    value: 60
  },
  render: args => <Progress {...args} className="w-80" />
}`,...r.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    value: 0
  },
  render: args => <Progress {...args} className="w-80" />
}`,...t.parameters?.docs?.source}}};l.parameters={...l.parameters,docs:{...l.parameters?.docs,source:{originalSource:`{
  args: {
    value: 100
  },
  render: args => <Progress {...args} className="w-80" />
}`,...l.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  render: () => <div className="space-y-4 w-80">
            {[{
      label: 'Storage',
      value: 72
    }, {
      label: 'Memory',
      value: 45
    }, {
      label: 'CPU',
      value: 28
    }, {
      label: 'Bandwidth',
      value: 93
    }].map(({
      label,
      value
    }) => <div key={label} className="space-y-1.5">
                    <div className="flex justify-between text-sm">
                        <span className="text-foreground font-medium">{label}</span>
                        <span className="text-muted-foreground">{value}%</span>
                    </div>
                    <Progress value={value} />
                </div>)}
        </div>
}`,...o.parameters?.docs?.source}}};const N=["Default","Empty","Full","MultipleStates"];export{r as Default,t as Empty,l as Full,o as MultipleStates,N as __namedExportsOrder,x as default};
