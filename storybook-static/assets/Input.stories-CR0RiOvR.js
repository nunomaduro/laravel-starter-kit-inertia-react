import{j as e}from"./jsx-runtime-u17CrQMm.js";import{I as i}from"./input-xwZcwifb.js";import{L as l}from"./label-C0TV2pJf.js";import"./utils-BQHNewu7.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-CBqaTKC_.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-C5dpwcrp.js";import"./index-dmJXtalC.js";const y={title:"Forms/Input",component:i,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{type:{control:"select",options:["text","email","password","number","search","tel","url"]},disabled:{control:"boolean"},placeholder:{control:"text"}}},r={args:{placeholder:"Enter text…",type:"text"}},a={render:()=>e.jsxs("div",{className:"grid w-72 gap-1.5",children:[e.jsx(l,{htmlFor:"email",children:"Email"}),e.jsx(i,{id:"email",type:"email",placeholder:"you@example.com"})]})},s={args:{placeholder:"Disabled input",disabled:!0,value:"Cannot edit this"}},t={args:{type:"password",placeholder:"Enter password"}},o={render:()=>e.jsxs("div",{className:"grid w-72 gap-1.5",children:[e.jsx(l,{htmlFor:"username",children:"Username"}),e.jsx(i,{id:"username","aria-invalid":!0,className:"border-destructive focus-visible:ring-destructive",defaultValue:"taken@"}),e.jsx("p",{className:"text-xs text-destructive",children:"Username is already taken."})]})};r.parameters={...r.parameters,docs:{...r.parameters?.docs,source:{originalSource:`{
  args: {
    placeholder: 'Enter text…',
    type: 'text'
  }
}`,...r.parameters?.docs?.source}}};a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:`{
  render: () => <div className="grid w-72 gap-1.5">
            <Label htmlFor="email">Email</Label>
            <Input id="email" type="email" placeholder="you@example.com" />
        </div>
}`,...a.parameters?.docs?.source}}};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {
    placeholder: 'Disabled input',
    disabled: true,
    value: 'Cannot edit this'
  }
}`,...s.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    type: 'password',
    placeholder: 'Enter password'
  }
}`,...t.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  render: () => <div className="grid w-72 gap-1.5">
            <Label htmlFor="username">Username</Label>
            <Input id="username" aria-invalid className="border-destructive focus-visible:ring-destructive" defaultValue="taken@" />
            <p className="text-xs text-destructive">Username is already taken.</p>
        </div>
}`,...o.parameters?.docs?.source}}};const w=["Default","WithLabel","Disabled","Password","WithError"];export{r as Default,s as Disabled,t as Password,o as WithError,a as WithLabel,w as __namedExportsOrder,y as default};
