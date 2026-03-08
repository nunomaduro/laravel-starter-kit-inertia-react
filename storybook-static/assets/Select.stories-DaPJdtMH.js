import{j as e}from"./jsx-runtime-u17CrQMm.js";import{L as o}from"./label-C0TV2pJf.js";import{S as n,a as c,b as s,c as i,d as t}from"./select-DZ-W-3UC.js";import"./utils-BQHNewu7.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-CBqaTKC_.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";import"./index-C5dpwcrp.js";import"./index-dmJXtalC.js";import"./chevron-down-CkowleEz.js";import"./createLucideIcon-Cq8_ABKM.js";import"./check-Bwm3_tfc.js";import"./index-CFnpPvE6.js";import"./index-BMy6K8Gt.js";import"./index-BMkthtSq.js";import"./index-Bet7iHPZ.js";import"./index-DegZLnM5.js";import"./index-BcoYK7RL.js";import"./index-BZKZbRGl.js";import"./index-DJ6Rm95R.js";import"./index-D7chfnjX.js";import"./index-DmKEutxk.js";const O={title:"Forms/Select",component:n,tags:["autodocs"],parameters:{layout:"centered"}},r={render:()=>e.jsxs("div",{className:"grid w-64 gap-1.5",children:[e.jsx(o,{children:"Country"}),e.jsxs(n,{children:[e.jsx(c,{children:e.jsx(s,{placeholder:"Select a country"})}),e.jsxs(i,{children:[e.jsx(t,{value:"us",children:"United States"}),e.jsx(t,{value:"gb",children:"United Kingdom"}),e.jsx(t,{value:"au",children:"Australia"}),e.jsx(t,{value:"ca",children:"Canada"}),e.jsx(t,{value:"de",children:"Germany"})]})]})]})},l={render:()=>e.jsxs(n,{defaultValue:"gb",children:[e.jsx(c,{className:"w-64",children:e.jsx(s,{})}),e.jsxs(i,{children:[e.jsx(t,{value:"us",children:"United States"}),e.jsx(t,{value:"gb",children:"United Kingdom"}),e.jsx(t,{value:"au",children:"Australia"})]})]})},a={render:()=>e.jsxs(n,{disabled:!0,children:[e.jsx(c,{className:"w-64",children:e.jsx(s,{placeholder:"Disabled"})}),e.jsx(i,{children:e.jsx(t,{value:"a",children:"Option A"})})]})};r.parameters={...r.parameters,docs:{...r.parameters?.docs,source:{originalSource:`{
  render: () => <div className="grid w-64 gap-1.5">
            <Label>Country</Label>
            <Select>
                <SelectTrigger>
                    <SelectValue placeholder="Select a country" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="us">United States</SelectItem>
                    <SelectItem value="gb">United Kingdom</SelectItem>
                    <SelectItem value="au">Australia</SelectItem>
                    <SelectItem value="ca">Canada</SelectItem>
                    <SelectItem value="de">Germany</SelectItem>
                </SelectContent>
            </Select>
        </div>
}`,...r.parameters?.docs?.source}}};l.parameters={...l.parameters,docs:{...l.parameters?.docs,source:{originalSource:`{
  render: () => <Select defaultValue="gb">
            <SelectTrigger className="w-64">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="us">United States</SelectItem>
                <SelectItem value="gb">United Kingdom</SelectItem>
                <SelectItem value="au">Australia</SelectItem>
            </SelectContent>
        </Select>
}`,...l.parameters?.docs?.source}}};a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:`{
  render: () => <Select disabled>
            <SelectTrigger className="w-64">
                <SelectValue placeholder="Disabled" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="a">Option A</SelectItem>
            </SelectContent>
        </Select>
}`,...a.parameters?.docs?.source}}};const E=["Default","WithPreselected","Disabled"];export{r as Default,a as Disabled,l as WithPreselected,E as __namedExportsOrder,O as default};
