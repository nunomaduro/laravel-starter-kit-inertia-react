import{j as e}from"./jsx-runtime-u17CrQMm.js";import{c}from"./utils-BQHNewu7.js";import{B as i}from"./badge-dtsZ3OQs.js";import"./index-yvwtsnL6.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-dmJXtalC.js";import"./index-LHNt3CwB.js";function n({usage:s,maxTokens:l,className:p}){const a=l?Math.min(s.total/l*100,100):null;return e.jsxs("div",{className:c("space-y-1.5",p),children:[e.jsxs("div",{className:"flex items-center gap-2 flex-wrap",children:[e.jsx("span",{className:"text-xs text-muted-foreground",children:"Tokens:"}),e.jsxs(i,{variant:"secondary",className:"h-4 px-1.5 text-[10px] gap-1",children:[e.jsx("span",{className:"text-muted-foreground",children:"prompt"}),s.prompt.toLocaleString()]}),e.jsxs(i,{variant:"secondary",className:"h-4 px-1.5 text-[10px] gap-1",children:[e.jsx("span",{className:"text-muted-foreground",children:"completion"}),s.completion.toLocaleString()]}),e.jsxs(i,{variant:"outline",className:"h-4 px-1.5 text-[10px] font-semibold",children:["total ",s.total.toLocaleString()]})]}),a!==null&&e.jsxs("div",{className:"space-y-0.5",children:[e.jsxs("div",{className:"flex items-center justify-between text-[10px] text-muted-foreground",children:[e.jsx("span",{children:"Context window"}),e.jsxs("span",{children:[a.toFixed(1),"%"]})]}),e.jsx("div",{className:"h-1 w-full rounded-full bg-muted overflow-hidden",children:e.jsx("div",{className:c("h-full rounded-full transition-all",a<60&&"bg-success",a>=60&&a<85&&"bg-warning",a>=85&&"bg-error"),style:{width:`${a}%`}})})]})]})}n.__docgenInfo={description:"Displays prompt / completion / total token counts.\nWhen `maxTokens` is provided, shows a usage progress bar.",methods:[],displayName:"TokenUsageDisplay",props:{usage:{required:!0,tsType:{name:"TokenUsage"},description:""},maxTokens:{required:!1,tsType:{name:"number"},description:"Maximum token budget to calculate percentage fill (optional)."},className:{required:!1,tsType:{name:"string"},description:""}}};const N={title:"AI/TokenUsage",component:n,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{maxTokens:{control:{type:"number"}}}},t={args:{usage:{prompt:1245,completion:382,total:1627}}},r={args:{usage:{prompt:2840,completion:710,total:3550},maxTokens:8192},render:s=>e.jsx("div",{className:"w-72",children:e.jsx(n,{...s})})},o={args:{usage:{prompt:7200,completion:600,total:7800},maxTokens:8192},render:s=>e.jsx("div",{className:"w-72",children:e.jsx(n,{...s})})};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    usage: {
      prompt: 1245,
      completion: 382,
      total: 1627
    }
  }
}`,...t.parameters?.docs?.source}}};r.parameters={...r.parameters,docs:{...r.parameters?.docs,source:{originalSource:`{
  args: {
    usage: {
      prompt: 2840,
      completion: 710,
      total: 3550
    },
    maxTokens: 8192
  },
  render: args => <div className="w-72"><TokenUsageDisplay {...args} /></div>
}`,...r.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    usage: {
      prompt: 7200,
      completion: 600,
      total: 7800
    },
    maxTokens: 8192
  },
  render: args => <div className="w-72"><TokenUsageDisplay {...args} /></div>
}`,...o.parameters?.docs?.source}}};const y=["Default","WithBudget","NearLimit"];export{t as Default,o as NearLimit,r as WithBudget,y as __namedExportsOrder,N as default};
